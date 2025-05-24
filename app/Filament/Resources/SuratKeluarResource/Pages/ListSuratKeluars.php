<?php

namespace App\Filament\Resources\SuratKeluarResource\Pages;

use App\Filament\Resources\SuratKeluarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuratKeluars extends ListRecords
{
    protected static string $resource = SuratKeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Surat Keluar'),
        ];
    }

    public function getHeading(): string
    {
        return "List Surat Keluar";
    }

    public function getSubheading(): string
    {
        return "Berikut adalah semua surat keluar yang telah diarsipkan.";
    }
}
