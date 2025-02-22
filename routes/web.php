<?php

use App\Http\Controllers\PdfController;
use App\Http\Controllers\SuratController;
use Illuminate\Support\Facades\Route;


Route::controller(SuratController::class)->group(function () {
    Route::get('/', [SuratController::class, 'index'])->name('dashboard');
    Route::get('/show', [SuratController::class, 'show'])->name('show');
    Route::get('/create', [SuratController::class, 'create'])->name('create');
    Route::get('/login', [SuratController::class, 'login'])->name('login');
    // Route::post('/scan', [SuratController::class, 'scan'])->name('scan');
    // Route::post('/preprocess', [SuratController::class, 'preprocessImages'])->name('preprocess');
});

// Route::get('/', function () {
//     return view('pdf-upload');
// });
// Route::post('/convert-pdf', [PdfController::class, 'convertPdfToImage']);
