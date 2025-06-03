<?php

namespace App\Filament\Resources\SuratMasukResource\Pages;

use App\Filament\Resources\SuratMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuratMasuks extends ListRecords
{
    protected static string $resource = SuratMasukResource::class;

    // public static function canAccess(): bool
    // {
    //     return Auth::user()->is_admin === true;
    // }

    public function getHeading(): string
    {
        return "List Surat Masuk";
    }

    public function getSubheading(): string
    {
        return "Berikut adalah semua surat masuk yang telah diarsipkan.";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Surat Masuk'),
        ];
    }
}
