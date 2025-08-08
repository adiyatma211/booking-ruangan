<?php

namespace App\Http\Controllers;

use App\Models\ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportRuanganController extends Controller
{
     public function index(Request $request)
    {
        // default range: bulan berjalan
        $start = $request->input('start', Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString());
        $end   = $request->input('end', Carbon::now('Asia/Jakarta')->endOfMonth()->toDateString());
        $ruangans = ruangan::orderBy('nama')->get(['id','nama','max_jam']);

        return view('report.ReportRuangan.v_report', compact('start','end','ruangans'));
    }

   public function data(Request $request)
{
    $request->validate([
        'start' => 'required|date',
        'end'   => 'required|date|after_or_equal:start',
        'ruangan_ids'   => 'nullable|array',
        'ruangan_ids.*' => 'integer|exists:ruangans,id',
        'consolidate'   => 'nullable|in:1',
        'group_by'      => 'nullable|in:room,pic,room_pic',
    ]);

    $start   = $request->start;
    $end     = $request->end;
    $groupBy = $request->input('group_by', 'room');
    $ruanganIds = $request->ruangan_ids ?? [];
    $consolidate = $request->boolean('consolidate');

    // Operasional harian
    $open  = '07:00:00';
    $close = '21:00:00';
    $operationalMinutesPerDay = Carbon::createFromTimeString($close)->diffInMinutes(Carbon::createFromTimeString($open));
    $daysCount = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;

    // Base query + join users jika perlu
    $q = DB::table('pemesanans as p')
        ->leftJoin('ruangans as r', 'r.id', '=', 'p.ruangan_id')
        ->leftJoin('users as u', 'u.id', '=', 'p.nama_pemesan_id')
        ->whereBetween('p.tanggal', [$start, $end]);

    if (!empty($ruanganIds)) {
        $q->whereIn('p.ruangan_id', $ruanganIds);
    }

    // Konsolidasi: total gabungan (abaikan group_by, tetap satu baris)
    if ($consolidate) {
        $row = $q->selectRaw('COUNT(*) as total_booking')
                 ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, CONCAT(p.tanggal," ",p.jam_mulai), CONCAT(p.tanggal," ",p.jam_selesai))) as total_minutes')
                 ->first();

        $totalMinutes = (int) ($row->total_minutes ?? 0);
        $totalBooking = (int) ($row->total_booking ?? 0);
        $avgMinutes   = $totalBooking ? ($totalMinutes / $totalBooking) : 0;

        $roomsCount = !empty($ruanganIds) ? count($ruanganIds) : DB::table('ruangans')->count();
        $capacityMinutes = $roomsCount * $daysCount * $operationalMinutesPerDay;
        $util = $capacityMinutes > 0 ? round(($totalMinutes / $capacityMinutes) * 100, 1) : 0;

        return response()->json([
            'meta' => [
                'start' => $start,
                'end'   => $end,
                'days'  => $daysCount,
                'operational_minutes_per_day' => $operationalMinutesPerDay,
                'consolidated' => true,
                'group_by' => $groupBy,
            ],
            'data' => [[
                'nama'            => 'Konsolidasi',
                'total_booking'   => $totalBooking,
                'total_jam'       => round($totalMinutes / 60, 2),
                'rata2_jam'       => round($avgMinutes / 60, 2),
                'utilization_pct' => $util,
            ]],
        ]);
    }

    // Grouping biasa
    if ($groupBy === 'room') {
        $rows = $q->select('p.ruangan_id','r.nama as room_name')
                  ->selectRaw('COUNT(*) as total_booking')
                  ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, CONCAT(p.tanggal," ",p.jam_mulai), CONCAT(p.tanggal," ",p.jam_selesai))) as total_minutes')
                  ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, CONCAT(p.tanggal," ",p.jam_mulai), CONCAT(p.tanggal," ",p.jam_selesai))) as avg_minutes')
                  ->groupBy('p.ruangan_id','r.nama')
                  ->orderBy('r.nama')
                  ->get();

        $data = $rows->map(function($row) use ($daysCount, $operationalMinutesPerDay) {
            $totalMinutes = (int) $row->total_minutes;
            $capacityMinutes = $daysCount * $operationalMinutesPerDay;
            $util = $capacityMinutes > 0 ? round(($totalMinutes / $capacityMinutes) * 100, 1) : 0;

            return [
                'nama'            => $row->room_name,
                'total_booking'   => (int) $row->total_booking,
                'total_jam'       => round($totalMinutes / 60, 2),
                'rata2_jam'       => round(((float)$row->avg_minutes) / 60, 2),
                'utilization_pct' => $util,
            ];
        });

    } elseif ($groupBy === 'pic') {
        // Group by PIC (user)
        $rows = $q->select('p.nama_pemesan_id','u.name as pic_name')
                  ->selectRaw('COUNT(*) as total_booking')
                  ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, CONCAT(p.tanggal," ",p.jam_mulai), CONCAT(p.tanggal," ",p.jam_selesai))) as total_minutes')
                  ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, CONCAT(p.tanggal," ",p.jam_mulai), CONCAT(p.tanggal," ",p.jam_selesai))) as avg_minutes')
                  ->groupBy('p.nama_pemesan_id','u.name')
                  ->orderBy('u.name')
                  ->get();

        // Utilization untuk PIC dihitung terhadap kapasitas SEMUA ruangan terpilih (atau semua ruangan)
        $roomsCount = !empty($ruanganIds) ? count($ruanganIds) : DB::table('ruangans')->count();
        $capacityMinutes = $roomsCount * $daysCount * $operationalMinutesPerDay;

        $data = $rows->map(function($row) use ($capacityMinutes) {
            $totalMinutes = (int) $row->total_minutes;
            $util = $capacityMinutes > 0 ? round(($totalMinutes / $capacityMinutes) * 100, 2) : 0;

            return [
                'nama'            => $row->pic_name ?? '(Tidak diketahui)',
                'total_booking'   => (int) $row->total_booking,
                'total_jam'       => round($totalMinutes / 60, 2),
                'rata2_jam'       => round(((float)$row->avg_minutes) / 60, 2),
                'utilization_pct' => $util,
            ];
        });

    } else { // room_pic
        $rows = $q->select('p.ruangan_id','r.nama as room_name','p.nama_pemesan_id','u.name as pic_name')
                  ->selectRaw('COUNT(*) as total_booking')
                  ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, CONCAT(p.tanggal," ",p.jam_mulai), CONCAT(p.tanggal," ",p.jam_selesai))) as total_minutes')
                  ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, CONCAT(p.tanggal," ",p.jam_mulai), CONCAT(p.tanggal," ",p.jam_selesai))) as avg_minutes')
                  ->groupBy('p.ruangan_id','r.nama','p.nama_pemesan_id','u.name')
                  ->orderBy('r.nama')
                  ->orderBy('u.name')
                  ->get();

        $capacityMinutes = $daysCount * $operationalMinutesPerDay; // kapasitas per RUANGAN
        $data = $rows->map(function($row) use ($capacityMinutes) {
            $totalMinutes = (int) $row->total_minutes;
            $util = $capacityMinutes > 0 ? round(($totalMinutes / $capacityMinutes) * 100, 2) : 0;

            return [
                'nama'            => $row->room_name.' — '.$row->pic_name,
                'total_booking'   => (int) $row->total_booking,
                'total_jam'       => round($totalMinutes / 60, 2),
                'rata2_jam'       => round(((float)$row->avg_minutes) / 60, 2),
                'utilization_pct' => $util,
            ];
        });
    }

    return response()->json([
        'meta' => [
            'start' => $start,
            'end'   => $end,
            'days'  => $daysCount,
            'operational_minutes_per_day' => $operationalMinutesPerDay,
            'consolidated' => false,
            'group_by' => $groupBy,
        ],
        'data' => $data,
    ]);
}


    // Optional: CSV export cepat (tanpa paket)
    public function exportCsv(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
            'ruangan_ids' => 'nullable|array',
            'ruangan_ids.*' => 'integer|exists:ruangans,id',
        ]);

        // Reuse endpoint data()
        $json = $this->data($request)->getData(true);
        $rows = $json['data'] ?? [];

        $filename = 'report-ruangan-'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output','w');
            fputcsv($out, ['Ruangan','Total Booking','Total Jam','Rata-rata Jam','Utilization %']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['ruangan'],
                    $r['total_booking'],
                    $r['total_jam'],
                    $r['rata2_jam'],
                    $r['utilization_pct'],
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
