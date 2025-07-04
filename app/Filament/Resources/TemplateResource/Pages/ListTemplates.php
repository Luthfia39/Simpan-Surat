<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemplates extends ListRecords
{
    protected static string $resource = TemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->hidden(),
        ];
    }

    public function getHeading(): string
    {
        return "Daftar Surat";
    }

    public function getSubheading(): string
    {
        return 'Pilih salah satu jenis surat di bawah ini untuk mulai membuat surat.';
    }
}
