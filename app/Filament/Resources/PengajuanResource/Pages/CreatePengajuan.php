<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;

    protected ?string $heading = "Buat pengajuan";

    protected ?string $subheading = "Isi data berikut untuk melakukan proses pengajuan surat";

    protected function mutateFormDataBeforeCreate(array $data): array
    {
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
        $actions = parent::getFormActions();

        // Iterasi dan modifikasi aksi
        $modifiedActions = array_map(function (Action $action) {
            switch ($action->getName()) {
                case 'create':
                    // Ubah label tombol 'Create' menjadi 'Buat Pengajuan'
                    return $action->label('Buat Pengajuan');
                case 'cancel':
                    // Ubah label tombol 'Cancel' menjadi 'Batal'
                    return $action->label('Batal');
                // Aksi 'createAndCreateAnother' akan difilter setelah map
            }
            return $action;
        }, $actions);

        return array_filter($modifiedActions, fn (Action $action) => $action->getName() !== 'createAnother');
    }
}
