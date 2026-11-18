<?php

use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\MahasiswaController;

Route::apiResource('kelas', KelasController::class);
Route::apiResource('mahasiswa', MahasiswaController::class);
