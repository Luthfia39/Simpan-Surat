<?php

use App\Http\Controllers\MongoController;
use App\Http\Controllers\SuratController;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

use Laravel\Socialite\Facades\Socialite;
use Filament\Facades\Filament;

Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/user/oauth/callback/google', function () {
    try {
        $googleUser = Socialite::driver('google')->user();

        if (!$googleUser->getEmail()) {
            abort(403, 'Email not provided by Google.');
        }

        $user = \App\Models\User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = \App\Models\User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(\Str::uuid()),
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'is_admin' => false,
                'nim' => null,
                'prodi' => null,
            ]);
        }

        Auth::login($user);

        // if ($user->is_admin && $user->email === 'luthfia.nisa2703@mail.ugm.ac.id') {
        //     return redirect('/admin'); // Redirect ke path panel admin Anda
        // } else {
            // return redirect('/user');  // Redirect ke path panel user Anda
        // }

        // Tentukan redirect berdasarkan peran user
        if ($user->is_admin) {
            // Redirect ke dashboard panel admin
            return redirect('/admin');
        } else {
            // Redirect ke dashboard panel user
            return redirect('/');
        }
    } catch (\Exception $e) {
        \Log::error('Google OAuth error: ' . $e->getMessage());
        return redirect('/login')->withErrors('Login Google gagal, silakan coba lagi.');
    }
    
});

Route::middleware('api')->prefix('api')->group(function () {
    Route::post('/hook', function (Request $request) {
        try {
            $data = $request->all();
            Log::info('DARI HOOK:', $data);

            // Access the original task_id and pdf_url, which apply to the whole PDF
            $taskId = $data['task_id'];
            $pdfUrl = $data['pdf_url'];

            // Check if 'processed_documents' exists and is an array
            if (isset($data['processed_documents']) && is_array($data['processed_documents'])) {
                foreach ($data['processed_documents'] as $document) {
                    $documentIndex = $document['document_index'] ?? 1;

                    // Create a new Surat record for each detected document
                    Surat::create([
                        'task_id' => $taskId, 
                        'pdf_url' => $pdfUrl, 
                        'is_ugm' => $document['is_ugm_format'],
                        'document_index' => $documentIndex, 
                        'letter_type' => $document['letter_type'],
                        'ocr_text' => $document['ocr_text'],
                        'extracted_fields' => json_encode($document['extracted_fields']),
                        'review_status' => 'pending_review'
                    ]);
                }
            } else {
                // Handle cases where 'processed_documents' might be missing or not an array
                // (e.g., if the Python script didn't detect any documents or sent an old format)
                Log::warning('DARI HOOK: "processed_documents" tidak ditemukan atau bukan array. Data:', $data);
                
                // You might choose to still create a record for the whole PDF
                // or return an error based on your application logic.
                // For now, let's assume if it's missing, it's an error from Python's side.
                return response()->json(['error' => 'Invalid data format: "processed_documents" missing or malformed'], 400);
            }

            return response()->json(['message' => 'Data diterima dan disimpan'], 200);
        } catch (\Exception $e) {
            Log::error('Error di hook: ' . $e->getMessage() . ' | Request Data: ' . json_encode($request->all()));
            return response()->json(['error' => 'Terjadi error saat memproses data'], 500);
        }
    });
});