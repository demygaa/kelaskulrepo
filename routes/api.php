<?php

use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\MahasiswaController;
use App\Http\Controllers\Api\KelasMahasiswaController;
use App\Http\Controllers\Api\PertemuanController;

Route::apiResource('kelas', KelasController::class);
Route::apiResource('mahasiswa', MahasiswaController::class);
Route::apiResource('kelasmahasiswa', KelasMahasiswaController::class);
Route::apiResource('pertemuan', PertemuanController::class);


// php artisan make:controller Api/KehadiranController
// php artisan make:controller Api/RekapController
