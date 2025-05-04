<?php

namespace App\Filament\Resources\SuratResource\Pages;

use App\Filament\Resources\SuratResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSurats extends ListRecords
{
    protected static string $resource = SuratResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }

    public function getHeading(): string
    {
        $user = auth()->user();
        return "Halo, {$user->name}";
    }

    public function getSubheading(): string
    {
        return 'Pilih salah satu template di bawah ini untuk mulai membuat surat';
    }
}
