<?php

use App\Http\Controllers\MongoController;
use App\Http\Controllers\SuratController;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

use Laravel\Socialite\Facades\Socialite;

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

        return redirect('/user'); // redirect ke panel Filament
    } catch (\Exception $e) {
        \Log::error('Google OAuth error: ' . $e->getMessage());
        return redirect('/login')->withErrors('Login Google gagal, silakan coba lagi.');
    }
    
});
