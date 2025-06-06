<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Illuminate\Support\Facades\Auth;

class ListPengajuans extends ListRecords
{
    protected static string $resource = PengajuanResource::class;

    protected ?string $heading = 'Pengajuan';

    protected ?string $subheading = 'Berikut adalah semua pengajuan Anda.';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Pengajuan Baru')
                ->hidden(Auth::user()->is_admin),
        ];
    }
}
