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
