<?php

use App\Http\Controllers\Api\KehadiranController;
use App\Http\Controllers\Api\KelasMahasiswaController;
use App\Http\Controllers\Api\RekapController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(__DIR__.'/api.php');

Route::name('api.')->group(function () {
    Route::get('kelas/{kelas}/mahasiswas', [KelasMahasiswaController::class, 'index'])->name('kelas.mahasiswas.index');
    Route::post('kelas/{kelas}/mahasiswas', [KelasMahasiswaController::class, 'store'])->name('kelas.mahasiswas.store');
    Route::delete('kelas/{kelas}/mahasiswas/{mahasiswa}', [KelasMahasiswaController::class, 'destroy'])->name('kelas.mahasiswas.destroy');
    Route::get('kelas/{kelas}/rekap', [RekapController::class, 'show'])->name('kelas.rekap');
    Route::get('pertemuans/{pertemuan}/kehadirans', [KehadiranController::class, 'index'])->name('pertemuans.kehadirans.index');
    Route::put('pertemuans/{pertemuan}/kehadirans', [KehadiranController::class, 'store'])->name('pertemuans.kehadirans.store');
});
