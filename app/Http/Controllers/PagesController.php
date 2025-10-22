<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\ruangan;
use App\Models\pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PagesController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();

        $roomsCount = Ruangan::whereNull('deleted_at')->count();
        $bookingsTodayCount = Pemesanan::whereDate('tanggal', $today)->count();

        $now = Carbon::now();
        $nextBooking = Pemesanan::with('ruangan', 'user')
            ->whereDate('tanggal', $today)
            ->where('jam_mulai', '>=', $now->format('H:i:s'))
            ->orderBy('jam_mulai')
            ->first();

        $bookedHoursToday = (float) Pemesanan::whereDate('tanggal', $today)->sum('durasi_jam');
        $capacityHours = max(1, $roomsCount) * 13; // asumsi jam operasi 08:00-21:00 (13 jam)
        $occupancy = $capacityHours > 0 ? round(($bookedHoursToday / $capacityHours) * 100) : 0;

        // Weekly usage (7 hari terakhir, termasuk hari ini)
        $weeklyLabels = [];
        $weeklySeries = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $sum = (float) Pemesanan::whereDate('tanggal', $day)->sum('durasi_jam');
            $weeklyLabels[] = $day->format('d M');
            $weeklySeries[] = round($sum, 2);
        }

        $upcoming = Pemesanan::with('ruangan', 'user')
            ->whereDate('tanggal', '>=', $today)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->limit(6)
            ->get();

        // Recent bookings (latest created)
        $recentBookings = Pemesanan::with('ruangan', 'user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Visitors Profile: komposisi jam terpakai per ruangan (hari ini)
        $visitorRaw = Pemesanan::join('ruangans', 'pemesanans.ruangan_id', '=', 'ruangans.id')
            ->whereDate('pemesanans.tanggal', $today)
            ->groupBy('pemesanans.ruangan_id', 'ruangans.nama')
            ->orderBy(DB::raw('SUM(pemesanans.durasi_jam)'), 'desc')
            ->limit(6)
            ->get([
                'ruangans.nama as nama',
                DB::raw('SUM(pemesanans.durasi_jam) as jam')
            ]);

        $visitorLabels = $visitorRaw->pluck('nama');
        $visitorSeries = $visitorRaw->pluck('jam')->map(function ($v) { return (float) $v; });

        return view('dashboard.v_dash', compact(
            'roomsCount',
            'bookingsTodayCount',
            'nextBooking',
            'bookedHoursToday',
            'occupancy',
            'weeklyLabels',
            'weeklySeries',
            'upcoming',
            'recentBookings',
            'visitorLabels',
            'visitorSeries'
        ));
    }

    // Booking 
    
    
    public function inquiryRuangan()
    {
        $today = Carbon::today();
    
        $ruangans = Ruangan::all()->map(function ($ruangan) use ($today) {
            // Ambil booking terakhir (jam_selesai terbesar) untuk hari ini
            $lastBooking = Pemesanan::where('ruangan_id', $ruangan->id)
                ->whereDate('tanggal', $today)
                ->orderByDesc('jam_selesai')
                ->first();
    
            $isFull = false;
    
            if ($lastBooking) {
                $jamSelesai = Carbon::createFromFormat('H:i:s', $lastBooking->jam_selesai);
                $jamBatas = Carbon::createFromTime(21, 0, 0); // 21:00
    
                if ($jamSelesai->greaterThanOrEqualTo($jamBatas)) {
                    $isFull = true;
                }
            }
    
            $ruangan->is_full = $isFull;
    
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
