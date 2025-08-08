<?php

namespace App\Http\Controllers;

use App\Models\ruangan;
use App\Models\pemesanan;
use Illuminate\Http\Request;

use Illuminate\Support\Carbon;
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
    $isSuperAdmin = method_exists($user, 'hasRole') ? $user->hasRole('SuperAdmin') : false;

    // Basic validation (no max durasi here; we enforce against ruangan->max_jam below)
    $validator = Validator::make($request->all(), [
        'ruangan_id'  => 'required|exists:ruangans,id',
        'tanggal'     => 'required|date',
        'keperluan'   => 'required|string',
        'jam_mulai'   => 'required|date_format:H:i',
        'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        'durasi'      => 'required|numeric|min:0.5',
        'keterangan'  => 'nullable|string',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $tz = 'Asia/Jakarta';
    $tanggal = Carbon::parse($request->tanggal, $tz)->toDateString();

    // Normalize times to same date
    $mulaiDT   = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$request->jam_mulai}", $tz);
    $selesaiDT = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$request->jam_selesai}", $tz);

    // Fetch ruangan for policy checks
    $ruangan = Ruangan::findOrFail($request->ruangan_id);

    // Enforce max_jam for non-superadmin
    if (!$isSuperAdmin) {
        if ((float)$request->durasi > (float)$ruangan->max_jam) {
            return response()->json([
                'status'  => false,
                'message' => "Durasi maksimal untuk ruangan ini adalah {$ruangan->max_jam} jam.",
            ], 422);
        }
    }

    // Enforce operational window 07:00–21:00 for non-superadmin
    if (!$isSuperAdmin) {
        $openDT  = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} 07:00", $tz);
        $closeDT = Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} 21:00", $tz);
        if ($mulaiDT < $openDT || $selesaiDT > $closeDT) {
            return response()->json([
                'status'  => false,
                'message' => 'Waktu pemesanan harus di antara 07:00–21:00.',
            ], 422);
        }
    }

    // Store as H:i:s
    $mulai   = $mulaiDT->format('H:i:s');
    $selesai = $selesaiDT->format('H:i:s');

    try {
        DB::beginTransaction();

        // Half-open overlap: existing_start < new_end AND existing_end > new_start
        $conflict = Pemesanan::where('ruangan_id', $ruangan->id)
            ->whereDate('tanggal', $tanggal)
            ->where(function ($q) use ($mulai, $selesai) {
                $q->where('jam_mulai', '<', $selesai)   // existing_start < new_end
                  ->where('jam_selesai', '>', $mulai);  // existing_end   > new_start
            })
            ->lockForUpdate()
            ->exists();

        if ($conflict) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Waktu pemesanan bertabrakan dengan jadwal lain.',
            ], 409);
        }

        Pemesanan::create([
            'ruangan_id'      => $ruangan->id,
            'tanggal'         => $tanggal,
            'keperluan'       => $request->keperluan,
            'jam_mulai'       => $mulai,
            'jam_selesai'     => $selesai,
            'durasi_jam'      => $request->durasi,
            'keterangan'      => $request->keterangan,
            'nama_pemesan_id' => $user->id,
        ]);

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Pemesanan berhasil disimpan.',
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'status'  => false,
            'message' => 'Terjadi kesalahan saat menyimpan.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
    // public function pemesananRuang(Request $request)
    // {
    //     $user = Auth::user();
    //     $isSuperAdmin = $user->hasRole('SuperAdmin'); // Gunakan method hasRole() dari Spatie/Role atau sesuaikan

    //     // Validasi umum
    //     $validator = Validator::make($request->all(), [
    //         'ruangan_id'   => 'required|exists:ruangans,id',
    //         'tanggal'      => 'required|date',
    //         'keperluan'    => 'required|string',
    //         'jam_mulai'    => 'required|date_format:H:i',
    //         'jam_selesai'  => 'required|date_format:H:i|after:jam_mulai',
    //         'durasi'       => 'required|numeric|min:0.5' . ($isSuperAdmin ? '' : '|max:2'),
    //         'keterangan'   => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Validasi gagal',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Tambahan validasi jam_selesai jika BUKAN superadmin
    //         if (!$isSuperAdmin && $request->jam_selesai > '21:00') {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Waktu pemesanan tidak boleh melebihi jam 21:00.'
    //             ], 422);
    //         }

    //         // Cek konflik waktu
    //         $conflict = Pemesanan::where('ruangan_id', $request->ruangan_id)
    //             ->where('tanggal', $request->tanggal)
    //             ->where(function ($query) use ($request) {
    //                 $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
    //                     ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
    //                     ->orWhere(function ($query) use ($request) {
    //                         $query->where('jam_mulai', '<=', $request->jam_mulai)
    //                             ->where('jam_selesai', '>=', $request->jam_selesai);
    //                     });
    //             })
    //             ->lockForUpdate()
    //             ->exists();

    //         if ($conflict) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Waktu pemesanan bertabrakan dengan jadwal lain.'
    //             ], 409);
    //         }

    //         // Simpan data
    //         Pemesanan::create([
    //             'ruangan_id'      => $request->ruangan_id,
    //             'tanggal'         => $request->tanggal,
    //             'keperluan'       => $request->keperluan,
    //             'jam_mulai'       => $request->jam_mulai,
    //             'jam_selesai'     => $request->jam_selesai,
    //             'durasi_jam'      => $request->durasi,
    //             'keterangan'      => $request->keterangan,
    //             'nama_pemesan_id' => $user->id,
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Pemesanan berhasil disimpan.'
    //         ], 200);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Terjadi kesalahan saat menyimpan.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getEvents()
    {
        $data = pemesanan::with('ruangan', 'user')->get(); // pastikan ada relasi user jika pakai Auth

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
