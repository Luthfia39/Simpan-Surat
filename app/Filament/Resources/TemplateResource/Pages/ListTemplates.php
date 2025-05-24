<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemplates extends ListRecords
{
    protected static string $resource = TemplateResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }

    public function getHeading(): string
    {
        return "Daftar Template Surat";
    }

    public function getSubheading(): string
    {
        return 'Pilih salah satu template di bawah ini untuk mulai membuat surat.';
    }
}
