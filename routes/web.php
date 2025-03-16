<?php

use App\Http\Controllers\MongoController;
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

Route::middleware('api')->prefix('api')->group(function () {
    Route::post('/surat', [MongoController::class, 'store']); // Menyimpan surat
    Route::get('/surat', [MongoController::class, 'index']); // Menampilkan semua surat
    Route::get('/surat/{id}', [MongoController::class, 'show']); // Menampilkan surat berdasarkan ID
    // Route::put('/surat/{id}', [MongoController::class, 'update']); // Mengupdate surat
    Route::delete('/surat/{id}', [MongoController::class, 'destroy']); // Menghapus surat
});

// Route::apiResource('/surat', MongoController::class);
