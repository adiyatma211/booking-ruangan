<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\Parameter\ParameterController;

Route::get('/', function () {
    return view('dashboard.v_dash');
});






Route::get(uri: '/reportRuangan', action: function () {
    return view(view: 'report.ReportRuangan.v_report');
});

Route::get(uri: '/kalender', action: function () {
    return view(view: 'booking.v_calender');
});


Route::prefix('booking')->middleware(['auth'])->group(function () {
    // Halaman awal (pilih ruangan)
    Route::get('/', [PagesController::class, 'inquiryRuangan'])->name('kalender');
    // Halaman kalender pemesanan per ruangan
    Route::get('/kalender/{ruangan}', [PemesananController::class, 'kalender'])->name('kalender');
    // Submit pemesanan
    Route::post('/store', [PemesananController::class, 'pemesananRuang'])->name('pemesanan.store');
    // Ambil data event (pemesanan) untuk FullCalendar
    Route::get('/events', [PemesananController::class, 'getEvents'])->name('pemesanan.events');
});




Route::prefix('ruangan')->group(function () {
    Route::get('/', [PagesController::class, 'getRuangan'])->name('ruangan.index');
    Route::post('/store', [ParameterController::class, 'simpanRuangan'])->name('ruangan.store');
    Route::put('/update/{id}', [ParameterController::class, 'updateRuangan'])->name('ruangan.update');
    Route::delete('/delete/{id}', [ParameterController::class, 'hapusRuangan'])->name('ruangan.destroy');
});
Route::get('/users', [PagesController::class, 'getUsers'])->name('users.index');
Route::post('/users/store', [ParameterController::class, 'TambahUsers'])->name('users.store');
Route::put('/users/update/{id}', [ParameterController::class, 'updateUsers'])->name('users.update');
Route::delete('/users/delete/{id}', [ParameterController::class, 'destroy'])->name('users.destroy');



Route::get('/roles',[PagesController::class,'getRoles']);
Route::post('/roles/tambah', [ParameterController::class, 'TambahRoles'])->name('parameter.tambah.roles');
Route::put('/roles/update/{id}', [ParameterController::class, 'UpdateRoles'])->name('parameter.update.roles');
Route::post('/roles/tambah', [ParameterController::class, 'TambahRoles'])->name('parameter.tambah.roles');
Route::delete('/roles/delete/{id}', [ParameterController::class, 'DeleteRole'])->name('parameter.delete.roles');





Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



require __DIR__.'/auth.php';
