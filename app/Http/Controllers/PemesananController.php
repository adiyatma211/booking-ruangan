<?php

namespace App\Http\Controllers;

use App\Models\ruangan;
use App\Models\pemesanan;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class PemesananController extends Controller
{
    

    // Tampilkan kalender untuk ruangan tertentu
    public function kalender($id)
    {
        $ruangan = ruangan::findOrFail($id);

        return view('booking.v_calender', compact('ruangan'));
    }
    public function pemesananRuang(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('SuperAdmin'); // Gunakan method hasRole() dari Spatie/Role atau sesuaikan
    
        // Validasi umum
        $validator = Validator::make($request->all(), [
            'ruangan_id'   => 'required|exists:ruangans,id',
            'tanggal'      => 'required|date',
            'keperluan'    => 'required|string',
            'jam_mulai'    => 'required|date_format:H:i',
            'jam_selesai'  => 'required|date_format:H:i|after:jam_mulai',
            'durasi'       => 'required|numeric|min:0.5' . ($isSuperAdmin ? '' : '|max:2'),
            'keterangan'   => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            DB::beginTransaction();
    
            // Tambahan validasi jam_selesai jika BUKAN superadmin
            if (!$isSuperAdmin && $request->jam_selesai > '21:00') {
                return response()->json([
                    'status' => false,
                    'message' => 'Waktu pemesanan tidak boleh melebihi jam 21:00.'
                ], 422);
            }
    
            // Cek konflik waktu
            $conflict = Pemesanan::where('ruangan_id', $request->ruangan_id)
                ->where('tanggal', $request->tanggal)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                        ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                        ->orWhere(function ($query) use ($request) {
                            $query->where('jam_mulai', '<=', $request->jam_mulai)
                                ->where('jam_selesai', '>=', $request->jam_selesai);
                        });
                })
                ->lockForUpdate()
                ->exists();
    
            if ($conflict) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Waktu pemesanan bertabrakan dengan jadwal lain.'
                ], 409);
            }
    
            // Simpan data
            Pemesanan::create([
                'ruangan_id'      => $request->ruangan_id,
                'tanggal'         => $request->tanggal,
                'keperluan'       => $request->keperluan,
                'jam_mulai'       => $request->jam_mulai,
                'jam_selesai'     => $request->jam_selesai,
                'durasi_jam'      => $request->durasi,
                'keterangan'      => $request->keterangan,
                'nama_pemesan_id' => $user->id,
            ]);
    
            DB::commit();
    
            return response()->json([
                'status' => true,
                'message' => 'Pemesanan berhasil disimpan.'
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEvents(Request $request)
    {
        $query = Pemesanan::with('ruangan', 'user'); // pastikan ada relasi user jika pakai Auth

        // Optional filter by ruangan_id if provided
        if ($request->filled('ruangan_id')) {
            $query->where('ruangan_id', $request->input('ruangan_id'));
        }

        $data = $query->get();
    
        $events = $data->map(function ($item) {
            return [
                'title' => 'Jam : ' . substr($item->jam_mulai, 0, 5) . ' - ' . substr($item->jam_selesai, 0, 5) . "\n" .'Agenda : ' . $item->keperluan,
                'start' => $item->tanggal . 'T' . $item->jam_mulai,
                'end'   => $item->tanggal . 'T' . $item->jam_selesai,
                'color' => $item->ruangan->warna,
                'extendedProps' => [
                    'keperluan' => $item->keperluan,
                    'jam'       => substr($item->jam_mulai, 0, 5) . ' - ' . substr($item->jam_selesai, 0, 5),
                    'pic'       => $item->user->name ?? 'Unknown',
                    'ruangan' => $item->ruangan->nama ?? 'Tidak Diketahui',
                    
                ]
            ];
        });
      
        
    
        return response()->json($events);
    }
    
}
