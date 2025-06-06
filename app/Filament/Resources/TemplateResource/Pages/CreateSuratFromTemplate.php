<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

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

    /**
     * Mengatur aksi tombol di bagian bawah form.
     * Kita akan filter untuk menghilangkan 'createAndCreateAnotherAction'.
     */
    protected function getFormActions(): array
    {
        // Panggil metode parent untuk mendapatkan aksi default
        $actions = parent::getFormActions();

        // Filter aksi untuk menghilangkan 'createAndCreateAnotherAction'
        return array_filter($actions, fn (Action $action) => $action->getName() !== 'createAnother');
    }

    /**
     * Mengatur URL redirect setelah record berhasil dibuat.
     * Diarahkan ke halaman daftar (index) dari resource.
     */
    protected function getRedirectUrl(): string
    {
        // Mengarahkan ke halaman daftar (index) dari resource saat ini
        return $this->getResource()::getUrl('index');
    }
}
