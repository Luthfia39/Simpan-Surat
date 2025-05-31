<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\UserPanelProvider::class,
    \SocialiteProviders\Manager\ServiceProvider::class,
    MongoDB\Laravel\MongoDBServiceProvider::class,
];
