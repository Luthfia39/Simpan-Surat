<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSuratFromTemplate extends CreateRecord
{
    
    protected static string $resource = TemplateResource::class;

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Template Berhasil Dibuat')
            ->body('Template surat baru telah berhasil ditambahkan.')
            ->success()
            ->send();
    }
}
