<?php

use App\Http\Controllers\MongoController;
use App\Http\Controllers\SuratController;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('screen.welcome');
});

// Route::get('/hook', function (Request $request) {
//     $data = $request->all();

//     dd('dari hook : ' . json_encode($data));

// $surat = Surat::where('pdf_url', $data['pdf_url'])->first();
// if ($surat) {
//     $surat->update([
//         'is_ugm_format' => $data['is_ugm_format'],
//         'letter_type' => $data['letter_type'],
//         'ocr_text' => $data['ocr_text'],
//         'extracted_fields' => json_encode($data['extracted_fields']),
//     ]);
// }

//     return response()->json(['success' => true]);
// });

Route::controller(SuratController::class)->group(function () {
    Route::post('/delete/{id}', [SuratController::class, 'destroy'])->name('delete');
    Route::get('/dashboard', [SuratController::class, 'index'])->name('dashboard');
    Route::get('/show', [SuratController::class, 'show'])->name('show');
    Route::get('/create', [SuratController::class, 'create'])->name('create');
    Route::get('/detail/{id}', [SuratController::class, 'detail'])->name('detail');
    Route::get('/download/{id}', [SuratController::class, 'download'])->name('download');
    Route::get('/login', [SuratController::class, 'login'])->name('login');
    // Route::post('/scan', [SuratController::class, 'scan'])->name('scan');
    // Route::post('/preprocess', [SuratController::class, 'preprocessImages'])->name('preprocess');
});

Route::middleware('api')->prefix('api')->group(function () {
    Route::post('/surat', [MongoController::class, 'store'])->name('surat.store'); // Menyimpan surat
    Route::get('/surat', [MongoController::class, 'index']); // Menampilkan semua surat
    Route::get('/surat/{id}', [MongoController::class, 'show']); // Menampilkan surat berdasarkan ID
    // Route::put('/surat/{id}', [MongoController::class, 'update']); // Mengupdate surat
    Route::delete('/surat/{id}', [MongoController::class, 'destroy']); // Menghapus surat
    Route::post('/hook', function (Request $request) {
        try {
            $data = $request->all();
            Log::info('DARI HOOK:', $data);

            Surat::create([
                'task_id' => $data['task_id'],
                'pdf_url' => $data['pdf_url'],
                // 'is_ugm_format' => $data['is_ugm_format'],
                'letter_type' => $data['letter_type'],
                'ocr_text' => $data['ocr_text'],
                'extracted_fields' => json_encode($data['extracted_fields']),
            ]);

            return response()->json(['message' => 'Diterima'], 200);
        } catch (\Exception $e) {
            Log::error('Error di hook: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi error'], 500);
        }
    });
});

Route::get('/pdf', function () {
    return view('templates.aktif-kuliah');
});


// Route::apiResource('/surat', MongoController::class);
