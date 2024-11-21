<?php

use App\Http\Controllers\SuratController;
use Illuminate\Support\Facades\Route;


Route::controller(SuratController::class)->group(function () {
    Route::get('/', [SuratController::class, 'index'])->name('home');
    Route::post('/scan', [SuratController::class, 'scan'])->name('scan');
});
