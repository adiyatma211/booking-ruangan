<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\ruangan;
use App\Models\pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PagesController extends Controller
{

    // Dashboard

 public function Dashboard()
{
    $user    = Auth::user();
    $isAdmin = method_exists($user, 'hasRole') ? $user->hasRole(['Admin', 'Super Admin']) : false;

    // === COUNTS (handle deleted_at NULL atau '')
    $ShowTotal = ruangan::where(function ($q) {
        $q->whereNull('deleted_at')->orWhere('deleted_at', '');
    })->count();

    $ShowTotalPemesanan = pemesanan::where(function ($q) {
        $q->whereNull('deleted_at')->orWhere('deleted_at', '');
    })->count();

    $ShowTotalUser = User::where(function ($q) {
        $q->whereNull('deleted_at')->orWhere('deleted_at', '');
    })->count();

    // === RECENT BOOKINGS (5 terbaru): admin lihat semua, selain itu hanya miliknya
    $recentBookings = Pemesanan::query()
        ->with(['ruangan:id,nama', 'pemesan:id,name']) // Pastikan relasi ada
        ->when(!$isAdmin, fn ($q) => $q->where('nama_pemesan_id', $user->id))
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();

    // === CHART: total booking per hari (30 hari terakhir), ikut filter role
    $tz    = 'Asia/Jakarta';
    $days  = 30;
    $start = Carbon::now($tz)->subDays($days - 1)->startOfDay();
    $end   = Carbon::now($tz)->endOfDay();

    $chartBase = DB::table('pemesanans')
        ->selectRaw('tanggal, COUNT(*) as jml')
        ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]);

    if (!$isAdmin) {
        $chartBase->where('nama_pemesan_id', $user->id);
    }

    $raw = $chartBase
        ->groupBy('tanggal')
        ->orderBy('tanggal')
        ->pluck('jml', 'tanggal'); // ['YYYY-MM-DD' => count]

    $bookingChartLabels = [];
    $bookingChartSeries = [];
    $cursor = $start->copy();
    while ($cursor <= $end) {
        $tgl = $cursor->toDateString();
        $bookingChartLabels[] = $tgl;
        $bookingChartSeries[] = (int) ($raw[$tgl] ?? 0); // zero-fill
        $cursor->addDay();
    }

    // === TABLE: “Pesanan ruangan Saya” (10 terbaru)
    $myBookings = pemesanan::query()
        ->with(['ruangan:id,nama'])
        ->where('nama_pemesan_id', $user->id)
        ->orderByDesc('tanggal')
        ->orderByDesc('jam_mulai')
        ->limit(10)
        ->get();

    return view('dashboard.v_dash', compact(
        'ShowTotal',
        'ShowTotalPemesanan',
        'ShowTotalUser',
        'recentBookings',
        'isAdmin',
        'bookingChartLabels',
        'bookingChartSeries',
        'myBookings'
    ));
}

    public function bookingChartData(Request $request)
{
    $request->validate([
        'start' => 'nullable|date',
        'end'   => 'nullable|date|after_or_equal:start',
    ]);

    $tz   = 'Asia/Jakarta';
    $user = Auth::user();
    $isAdmin = method_exists($user, 'hasRole') ? $user->hasRole(['Admin','Super Admin']) : false;

    // default: 14 hari terakhir
    $start = $request->filled('start')
        ? Carbon::parse($request->start, $tz)->startOfDay()
        : Carbon::now($tz)->subDays(13)->startOfDay();

    $end = $request->filled('end')
        ? Carbon::parse($request->end, $tz)->endOfDay()
        : Carbon::now($tz)->endOfDay();

    // Ambil count per tanggal dari tabel pemesanans
    $base = DB::table('pemesanans')
        ->selectRaw('tanggal, COUNT(*) as jml')
        ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]);

    if (!$isAdmin) {
        $base->where('nama_pemesan_id', $user->id);
    }

    $raw = $base
        ->groupBy('tanggal')
        ->orderBy('tanggal')
        ->pluck('jml','tanggal'); // ['YYYY-MM-DD' => count]

    // Isi tanggal kosong dengan 0
    $labels = [];
    $data   = [];
    $cursor = $start->copy();
    while ($cursor <= $end) {
        $tgl = $cursor->toDateString();
        $labels[] = $tgl;
        $data[]   = (int) ($raw[$tgl] ?? 0);
        $cursor->addDay();
    }



    return response()->json([
        'labels' => $labels,
        'series' => $data,
        'meta'   => [
            'start' => $start->toDateString(),
            'end'   => $end->toDateString(),
            'count' => array_sum($data),
        ]
    ]);
}
    // Booking
public function getCalender(){
    return view('booking.v_calender');
}

public function inquiryRuangan()
{
    $tz     = 'Asia/Jakarta';
    $today  = Carbon::today($tz);
    $openDT = Carbon::createFromFormat('Y-m-d H:i', $today->toDateString().' 07:00', $tz);
    $closeDT= Carbon::createFromFormat('Y-m-d H:i', $today->toDateString().' 21:00', $tz);

    $ruangans = ruangan::all()->map(function ($ruangan) use ($today, $tz, $openDT, $closeDT) {

        // 1) Ambil booking hari ini & klip ke jam operasional (07:00–21:00 pada tanggal yang sama)
        $bookings = pemesanan::where('ruangan_id', $ruangan->id)
            ->whereDate('tanggal', $today)
            ->orderBy('jam_mulai')
            ->get(['jam_mulai','jam_selesai']);

        $intervals = [];
        foreach ($bookings as $b) {
            $s = Carbon::createFromFormat('Y-m-d H:i:s', $today->toDateString().' '.$b->jam_mulai, $tz);
            $e = Carbon::createFromFormat('Y-m-d H:i:s', $today->toDateString().' '.$b->jam_selesai, $tz);

            if ($e <= $openDT || $s >= $closeDT) continue;
            if ($s < $openDT)  $s = $openDT->copy();
            if ($e > $closeDT) $e = $closeDT->copy();
            if ($e <= $s) continue;

            $intervals[] = [$s, $e];
        }

        // 2) Kalau tidak ada booking → satu gap penuh
        if (empty($intervals)) {
            $gapMin = $closeDT->diffInMinutes($openDT);
            $ruangan->is_full            = false;
            $ruangan->merged_bookings    = [];
            $ruangan->available_gaps     = [[
                'from'    => $openDT->format('H:i'),
                'to'      => $closeDT->format('H:i'),
                'minutes' => $gapMin,
            ]];
            $ruangan->first_gap          = $ruangan->available_gaps[0];
            $ruangan->next_available_at  = $ruangan->first_gap['from'];
            $ruangan->total_free_minutes = $gapMin;
            $ruangan->tooltip_text       = "Booking:\n—\n\n tersedia:\n{$openDT->format('H:i')}–{$closeDT->format('H:i')}";
            return $ruangan;
        }

        // 3) Merge interval yang overlap/menyentuh
        usort($intervals, fn($a,$b) => $a[0] <=> $b[0]);
        $merged = [];
        foreach ($intervals as $iv) {
            if (!$merged) { $merged[] = $iv; continue; }
            [$ls,$le] = $merged[count($merged)-1];
            [$cs,$ce] = $iv;

            if ($le >= $cs) { // overlap/bersinggungan
                if ($ce > $le) $merged[count($merged)-1][1] = $ce;
            } else {
                $merged[] = $iv;
            }
        }

        // 4) Hitung semua gap (untuk ditampilkan)
        $gaps = [];
        if ($merged[0][0]->gt($openDT)) $gaps[] = [$openDT->copy(), $merged[0][0]->copy()];
        for ($i=0; $i<count($merged)-1; $i++) {
            $endCur    = $merged[$i][1];
            $startNext = $merged[$i+1][0];
            if ($startNext->gt($endCur)) $gaps[] = [$endCur->copy(), $startNext->copy()];
        }
        $lastEnd = $merged[count($merged)-1][1];
        if ($closeDT->gt($lastEnd)) $gaps[] = [$lastEnd->copy(), $closeDT->copy()];

        // Payload untuk UI
        $availableGaps = [];
        $totalFree = 0;
        $largestGap = 0;
        foreach ($gaps as [$gs,$ge]) {
            $len = abs($ge->diffInMinutes($gs, false));
            $availableGaps[] = [
                'from'    => $gs->format('H:i'),
                'to'      => $ge->format('H:i'),
                'minutes' => $len,
            ];
            $totalFree  += $len;
            if ($len > $largestGap) $largestGap = $len;
        }

        $mergedBookings = array_map(function($m){
            [$s,$e] = $m;
            return ['from'=>$s->format('H:i'), 'to'=>$e->format('H:i')];
        }, $merged);

        // 5) Flag PENUH berbasis max_jam ruangan
        $maxSlotMinutes = max(1, (int)$ruangan->max_jam) * 60;   // jam → menit
        $canFitMax      = ($largestGap >= $maxSlotMinutes);
        $isFull         = !$canFitMax;

        // Tooltip text yang rapi
        $bookLines = collect($mergedBookings)->map(fn($b)=> $b['from'].'–'.$b['to'])->implode("\n");
        $gapLines  = collect($availableGaps)->map(fn($g)=> $g['from'].'–'.$g['to'])->implode("\n");
        $tooltip   = "Booking:\n".($bookLines ?: '—')."\n\ntersedia:\n".($gapLines ?: '—');

        // 6) inject ke model (non-persisten)
        $firstGap = $availableGaps[0] ?? null;
        $ruangan->is_full            = $isFull;
        $ruangan->can_fit_max_slot   = $canFitMax;
        $ruangan->merged_bookings    = $mergedBookings;
        $ruangan->available_gaps     = $availableGaps;
        $ruangan->first_gap          = $firstGap;
        $ruangan->next_available_at  = $firstGap['from'] ?? null;
        $ruangan->total_free_minutes = $totalFree;
        $ruangan->tooltip_text       = $tooltip;

        return $ruangan;
    });

    return view('booking.v_book', compact('ruangans'));
}
    // Users

    public function getUsers()
    {
        $GetUsers = User::whereNull('deleted_at')->orderBy('id', 'desc')->get();
        $GetRoles = Role::whereNull('deleted_at')->orderBy('id', 'desc')->get();

        return view('parameter.Users.v_users', compact('GetUsers', 'GetRoles'));
    }

    // Roles
    public function getRoles()
    {
        $GetRoles = Role::whereNull('deleted_at')->orderBy('id', 'desc')->get();
        return view("parameter.Roles.v_roles", compact('GetRoles'));
    }

    // Ruangan
    public function getRuangan()
    {
        $ruangans = ruangan::whereNull('deleted_at')->orderBy('id', 'desc')->get();
        return view('parameter.Ruangan.v_ruangan', compact('ruangans'));
    }
}
