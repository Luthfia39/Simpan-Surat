<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\UserPanelProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    \SocialiteProviders\Manager\ServiceProvider::class,
    MongoDB\Laravel\MongoDBServiceProvider::class,
];
