<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Policies\SuratPolicy;
use App\Policies\TemplatePolicy;
use App\Policies\SuratKeluarPolicy;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

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
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        });

        Gate::define('view-surat-masuk', [SuratPolicy::class, 'view']);
        Gate::define('view-template', [TemplatePolicy::class, 'view']);
        Gate::define('view-surat-keluar', [SuratKeluarPolicy::class, 'view']);
    }
}
