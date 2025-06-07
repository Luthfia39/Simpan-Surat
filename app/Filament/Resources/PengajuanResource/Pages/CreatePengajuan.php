<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Jika user bukan admin, dan field 'status' tidak ada dalam data (karena disembunyikan),
        // maka set nilai default 'pending'.
        if (!auth()->user()->is_admin) {
            if (!isset($data['user_id'])) {
                $data['user_id'] = auth()->user()->id;
            }
            if (!isset($data['status'])) {
                $data['status'] = 'pending';
            }
            if (!isset($data['keterangan'])) {
                $data['keterangan'] = null;
            }
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        // Panggil metode parent untuk mendapatkan aksi default
        $actions = parent::getFormActions();

        // Filter aksi untuk menghilangkan 'createAndCreateAnotherAction'
        return array_filter($actions, fn (Action $action) => $action->getName() !== 'createAnother');
    }
}
