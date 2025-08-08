<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\ruangan;
use App\Models\pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PagesController extends Controller
{

    // Booking


public function inquiryRuangan()
{
    $today = Carbon::today('Asia/Jakarta');
    $open  = Carbon::createFromTime(7, 0, 0, 'Asia/Jakarta');
    $close = Carbon::createFromTime(21, 0, 0, 'Asia/Jakarta');

    $ruangans = Ruangan::all()->map(function ($ruangan) use ($today, $open, $close) {
        // --- Ambil booking hari ini, urut, dan klip ke jam operasional ---
        $bookings = Pemesanan::where('ruangan_id', $ruangan->id)
            ->whereDate('tanggal', $today)
            ->orderBy('jam_mulai')
            ->get(['jam_mulai','jam_selesai']);

        $intervals = [];
        foreach ($bookings as $b) {
            $s = Carbon::createFromFormat('H:i:s', $b->jam_mulai, 'Asia/Jakarta');
            $e = Carbon::createFromFormat('H:i:s', $b->jam_selesai, 'Asia/Jakarta');
            if ($e <= $open || $s >= $close) continue;      // di luar operasional
            if ($s < $open)  $s = $open->copy();            // klip kiri
            if ($e > $close) $e = $close->copy();           // klip kanan
            if ($e <= $s) continue;                         // jaga-jaga
            $intervals[] = [$s, $e];
        }

        // --- Tidak ada booking → satu gap penuh 07–21 ---
        if (empty($intervals)) {
            $gapMinutes = $close->diffInMinutes($open);
            $ruangan->is_full           = false;
            $ruangan->merged_bookings   = [];
            $ruangan->available_gaps    = [[
                'from'    => $open->format('H:i'),
                'to'      => $close->format('H:i'),
                'minutes' => $gapMinutes,
            ]];
            $ruangan->first_gap         = $ruangan->available_gaps[0];
            $ruangan->next_available_at = $ruangan->first_gap['from'];
            $ruangan->total_free_minutes= $gapMinutes;

            // Tooltip teks rapi (pakai \n untuk line break)
            $ruangan->tooltip_text = "Booking:\n—\n\nSlot tersedia:\n{$open->format('H:i')}–{$close->format('H:i')}";
            return $ruangan;
        }

        // --- Merge interval yang overlap/menyentuh ---
        usort($intervals, fn($a,$b) => $a[0] <=> $b[0]);
        $merged = [];
        foreach ($intervals as $iv) {
            if (!$merged) { $merged[] = $iv; continue; }
            [$ls,$le] = $merged[count($merged)-1];
            [$cs,$ce] = $iv;

            if ($le >= $cs) {                    // overlap / bersinggungan
                if ($ce > $le) $merged[count($merged)-1][1] = $ce;  // perluas
            } else {
                $merged[] = $iv;
            }
        }

        // --- Hitung gaps (slot kosong) dalam [open, close] ---
        $gaps = [];
        if ($merged[0][0]->gt($open)) $gaps[] = [$open->copy(), $merged[0][0]->copy()];
        for ($i=0; $i<count($merged)-1; $i++) {
            $endCur = $merged[$i][1];
            $startNext = $merged[$i+1][0];
            if ($startNext->gt($endCur)) $gaps[] = [$endCur->copy(), $startNext->copy()];
        }
        $lastEnd = end($merged)[1];
        if ($close->gt($lastEnd)) $gaps[] = [$lastEnd->copy(), $close->copy()];

        // --- Flag penuh: booking menutup 07–21 tanpa celah (1 blok saja) ---
        $coversAll = ($merged[0][0]->lte($open) && end($merged)[1]->gte($close) && count($merged) === 1);
        $isFull = $coversAll;

        // --- Siapkan payload rapi untuk UI ---
        $availableGaps = [];
        $totalFree = 0;
        foreach ($gaps as [$gs,$ge]) {
            $len = abs($ge->diffInMinutes($gs, false));
            $availableGaps[] = [
                'from'    => $gs->format('H:i'),
                'to'      => $ge->format('H:i'),
                'minutes' => $len,
            ];
            $totalFree += $len;
        }

        $mergedBookings = array_map(function($m){
            [$s,$e] = $m;
            return ['from'=>$s->format('H:i'), 'to'=>$e->format('H:i')];
        }, $merged);

        $firstGap = $availableGaps[0] ?? null;
        $nextAvailableAt = $firstGap['from'] ?? null;

        // Tooltip text (rapi, multi-baris)
        $bookLines = collect($mergedBookings)->map(fn($b)=> $b['from'].'–'.$b['to'])->implode("\n");
        $gapLines  = collect($availableGaps)->map(fn($g)=> $g['from'].'–'.$g['to'])->implode("\n");
        $tooltip   = "Booking:\n".($bookLines ?: '—')."\n\nSlot tersedia:\n".($gapLines ?: '—');

        // --- Inject payload ke model (non-persisten) ---
        $ruangan->is_full            = $isFull;
        $ruangan->merged_bookings    = $mergedBookings;
        $ruangan->available_gaps     = $availableGaps;
        $ruangan->first_gap          = $firstGap;
        $ruangan->next_available_at  = $nextAvailableAt;
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
