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
    
        // Ambil semua ruangan
        $ruangans = Ruangan::all();
    
        // Cek apakah ruangan penuh hari ini
        $ruangans->map(function ($ruangan) use ($today) {
            // Hitung total durasi booking hari ini untuk ruangan tersebut
            $totalDurasi = pemesanan::where('ruangan_id', $ruangan->id)
                ->whereDate('tanggal', $today)
                ->sum('durasi_jam');
        
            // Cek apakah sudah melebihi batas maksimal
            $ruangan->is_full = $totalDurasi >= $ruangan->max_jam;
        
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
