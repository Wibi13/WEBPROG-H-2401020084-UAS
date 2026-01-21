<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;

Route::get('/', [AbsensiController::class, 'index']);
Route::post('/check-in', [AbsensiController::class, 'checkIn']);
Route::post('/check-out', [AbsensiController::class, 'checkOut']);
Route::post('/absensi/reset', [AbsensiController::class, 'resetToday']);