<?php

use App\Http\Controllers\PdfController;
use App\Http\Controllers\SuratController;
use Illuminate\Support\Facades\Route;


Route::controller(SuratController::class)->group(function () {
    Route::get('/', [SuratController::class, 'index'])->name('home');
    Route::post('/scan', [SuratController::class, 'scan'])->name('scan');
    Route::get('/coba', [SuratController::class, 'getData'])->name('data');
    Route::get('/proses', [SuratController::class, 'preprocessImages'])->name('preprocess');
    // Route::post('/preprocess', [SuratController::class, 'preprocessImages'])->name('preprocess');
});

// Route::get('/', function () {
//     return view('pdf-upload');
// });
// Route::post('/convert-pdf', [PdfController::class, 'convertPdfToImage']);
