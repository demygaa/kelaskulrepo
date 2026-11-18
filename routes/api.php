<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\MahasiswaController;
use App\Http\Controllers\Api\KelasMahasiswaController;
use App\Http\Controllers\Api\PertemuanController;
use App\Http\Controllers\Api\KehadiranController;
use App\Http\Controllers\Api\RekapController;


Route::apiResource('kelas', KelasController::class)
    ->parameters(['kelas' => 'kelas']);

Route::apiResource('mahasiswas', MahasiswaController::class);

Route::get('kelas/{kelas}/mahasiswas', [KelasMahasiswaController::class, 'index']);
Route::post('kelas/{kelas}/mahasiswas', [KelasMahasiswaController::class, 'store']);
Route::delete('kelas/{kelas}/mahasiswas/{mahasiswa}', [KelasMahasiswaController::class, 'destroy']);

Route::apiResource('kelas.pertemuans', PertemuanController::class)
    ->parameters(['kelas' => 'kelas'])
    ->shallow();

Route::get('pertemuans/{pertemuan}/kehadirans', [KehadiranController::class, 'index']);
Route::put('pertemuans/{pertemuan}/kehadirans', [KehadiranController::class, 'store']);

Route::get('kelas/{kelas}/rekap', [RekapController::class, 'show']);