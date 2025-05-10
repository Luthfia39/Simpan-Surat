<?php

namespace App\Filament\Resources\SuratMasukResource\Pages;

use App\Filament\Resources\SuratMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Page;
use App\Models\Surat;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class EditSuratMasuk extends Page
{
    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.edit';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    use InteractsWithForms;

    public function mount(): void
    {
        $ocrData = Session::get('flask_response', null);

        $this->form->fill([
            'ocr_text' => $ocrData['ocr_text'],
            'letter_type' => $ocrData['letter_type'],
            'nomor_surat' => $ocrData['extracted_fields']['nomor_surat'] ?? '',
            'pengirim' => $ocrData['extracted_fields']['pengirim'] ?? '',
            'penandatangan' => $ocrData['extracted_fields']['penandatangan'] ?? '',
        ]);
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Hidden::make('ocr_text'),
                \Filament\Forms\Components\TextInput::make('nomor_surat')->label('Nomor Surat'),
                \Filament\Forms\Components\TextInput::make('pengirim')->label('Pengirim'),
                \Filament\Forms\Components\TextInput::make('penandatangan')->label('Penanda Tangan'),
            ])
            ->statePath('data');
    }

    public function save()
    {
        $data = $this->form->getState();

        // Simpan ke MongoDB
        $surat = new Surat();
        $surat->fill([
            'pdf_path' => Session::get('uploaded_file_path'),
            'ocr_text' => $data['ocr_text'],
            'letter_type' => $data['letter_type'],
            'extracted_fields' => [
                'nomor_surat' => $data['nomor_surat'],
                'pengirim' => $data['pengirim'],
                'penandatangan' => $data['penandatangan'],
            ]
        ]);

        $surat->save();

        Notification::make()
            ->title('Surat berhasil disimpan')
            ->success()
            ->send();

        return redirect()->to(route('filament.admin.resources.surat-masuks.index'));
    }
}
