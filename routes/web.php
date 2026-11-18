<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('page.kelas');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/kelas', [PageController::class, 'kelas'])->name('page.kelas');
    Route::get('/mahasiswas', [PageController::class, 'mahasiswa'])->name('page.mahasiswa');
    Route::get('/kelas/{kelas}/peserta', [PageController::class, 'peserta'])->name('page.peserta');
    Route::get('/kelas/{kelas}/pertemuans', [PageController::class, 'pertemuan'])->name('page.pertemuan');
    Route::get('/pertemuans/{pertemuan}/kehadiran', [PageController::class, 'kehadiran'])->name('page.kehadiran');
    Route::get('/kelas/{kelas}/rekap', [PageController::class, 'rekap'])->name('page.rekap');
});

require __DIR__.'/auth.php';
