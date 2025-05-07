<?php

namespace App\Filament\Resources\SuratMasukResource\Pages;

use App\Filament\Resources\SuratMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Surat;

class EditSuratMasuk extends EditRecord
{
    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.edit';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function addHighlight($original, $corrected, $type)
{
    Surat::create([
        'letter_id' => $this->letter->id,
        'original_text' => $original,
        'corrected_text' => $corrected,
        'highlight_type' => $type,
    ]);

    $this->highlights = Surat::where('letter_id', $this->letter->id)->get();
}
}
