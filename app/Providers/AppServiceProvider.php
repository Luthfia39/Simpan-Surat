<?php

namespace App\Providers;

use DutchCodingCompany\FilamentSocialite;
use DutchCodingCompany\FilamentSocialite\Models\Contracts\FilamentSocialiteUser as FilamentSocialiteUserContract;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ini_set('max_execution_time', 600);
        // set_time_limit(600);
        // FilamentSocialite::setLoginRedirectCallback(function (string $provider, FilamentSocialiteUserContract $socialiteUser) {
        //     return Filament::getPanelUrl();
        // });

        // Blade::anonymousComponentNamespace('surat.components', 'surat');
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        });
    }
}
