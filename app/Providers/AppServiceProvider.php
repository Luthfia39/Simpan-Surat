<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Policies\SuratPolicy;
use App\Policies\TemplatePolicy;
use App\Policies\SuratKeluarPolicy;
use App\Policies\PengajuanPolicy;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use App\Http\Responses\LogoutResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (env(key: 'APP_ENV') === 'local' && request()->server(key: 'HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme(scheme: 'https');
        };
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
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
        Gate::define('view-pengajuan', [PengajuanPolicy::class, 'view']);
        Gate::define('create-pengajuan', [PengajuanPolicy::class, 'create']);
        Gate::define('edit-pengajuan', [PengajuanPolicy::class, 'update']);
        Gate::define('viewAny-pengajuan', [PengajuanPolicy::class, 'viewAny']);
    }
}
