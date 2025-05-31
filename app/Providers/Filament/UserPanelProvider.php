<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Support\Colors;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Illuminate\Contracts\Auth\Authenticatable;

use App\Filament\Pages\Auth\Login;
use Illuminate\Support\Facades\Auth;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('user')
            ->path('user')
            // ->login()
            ->login(Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->brandName('SuratTEDI')
            ->darkMode(false)
            ->font('Plus Jakarta Sans')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->breadcrumbs(false);
            // ->plugin(
            //     FilamentSocialitePlugin::make()
            //         // (required) Add providers corresponding with providers in `config/services.php`. 
            //         ->providers([
            //             // Create a provider 'gitlab' corresponding to the Socialite driver with the same name.
            //             Provider::make('google')
            //                 ->label('Login dengan Google')
            //                 ->icon('heroicon-o-user-circle')
            //                 ->color(Color::Blue)
            //                 ->outlined(false)
            //                 ->stateless(false)
            //                 ->scopes([
            //                     'openid',
            //                     'email',
            //                     'profile',
            //                 ])
            //                 ->with(['...']),
            //         ])
            //         // (optional) Override the panel slug to be used in the oauth routes. Defaults to the panel ID.
            //         // ->slug('user')
            //         // (optional) Change the associated model class.
            //         ->userModelClass(\App\Models\User::class)
            //         ->createUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {

            //             print('bisa');

            //             dd(["user" => $oauthUser]);
            //             // Logic to create a new user.
            //             // $user = User::where('email', $oauthUser->getEmail())->first();

            //             // \Log::info(['user' =>$user]);

            //             // if (!$user) {

            //             //     \Log::info(['nama' => $oauthUser->getName()]);

            //             //     $userData = [
            //             //         'name' => $oauthUser->getName(),
            //             //         'email' => $oauthUser->getEmail(),
            //             //         'password' => Hash::make(Str::uuid()), // Set password ke UUID acak yang di-hash
            //             //         'email_verified_at' => now(),
            //             //         'google_id' => $oauthUser->getId(),
            //             //         'google_avatar' => $oauthUser->getAvatar(),
            //             //         'is_admin' => false,
            //             //         'nim' => null,
            //             //         'prodi' => null,
            //             //     ];

            //             //     $user = User::create($userData);
            //             // } else {
            //             //     // ... logika update user jika diperlukan
            //             // }

            //             if (! $user) {
            //                 $user = User::create([
            //                     'name' => $oauthUser->getName(),
            //                     'email' => $oauthUser->getEmail(),
            //                     'password' => bcrypt(Str::uuid()),
            //                     'email_verified_at' => now(),
            //                     'google_id' => $oauthUser->getId(),
            //                     'google_avatar' => $oauthUser->getAvatar(),
            //                 ]);
            //             }
                    
            //             // ✅ LOGIN langsung, jangan pakai SQL-based Auth
            //             Auth::login($user);

            //             return $user;
            //         })
            //         ->redirectAfterLoginUsing(function (string $provider, FilamentSocialiteUserContract $socialiteUser, FilamentSocialitePlugin $plugin) {
            //             // Change the redirect behaviour here.
            //             return Filament::getPanelUrl();
            //         })
            // );
    }
};
