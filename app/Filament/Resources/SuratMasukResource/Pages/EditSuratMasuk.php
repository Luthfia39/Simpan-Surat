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
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class EditSuratMasuk extends Page
{
    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.edit';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public ?string $taskId = null;
    public ?object $record = null;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    use InteractsWithForms;

    public function mount(): void
    {
        $this->taskId = Request::query('task_id') ?? Session::get('current_task_id');

        $ocrData = Surat::where('task_id', $this->taskId)->first();

        $this->form->fill([
            'pdf_path' => $ocrData['pdf_url'],
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
                \Filament\Forms\Components\Hidden::make('pdf_path'),
                \Filament\Forms\Components\Hidden::make('ocr_text'),
                \Filament\Forms\Components\Select::make('letter_type')->label('Jenis Surat')->options([
                    'Surat Pernyataan' => 'Surat Pernyataan',
                    'Surat Keterangan' => 'Surat Keterangan',
                    'Surat Tugas' => 'Surat Tugas',
                ]),
                \Filament\Forms\Components\TextInput::make('nomor_surat')->label('Nomor Surat'),
                \Filament\Forms\Components\TextInput::make('pengirim')->label('Pengirim'),
                \Filament\Forms\Components\TextInput::make('penandatangan')->label('Penanda Tangan'),
            ])
            ->statePath('data');
    }

    public function save()
    {
        $data = $this->form->getState();
        $url = Session::get('current_url');

        // Simpan ke MongoDB
        $surat = Surat::where('task_id', $this->taskId)->firstOrNew();

        $surat->fill([
            'task_id' => $this->taskId,
            'ocr_text' => $data['ocr_text'],
            'letter_type' => $data['letter_type'],
            'extracted_fields' => [
                'nomor_surat' => $data['nomor_surat'],
                'pengirim' => $data['pengirim'],
                'penandatangan' => $data['penandatangan'],
            ],
            'pdf_url' => $data['pdf_path'],
        ]);

        $surat->save();

        Notification::make()
            ->title('Surat berhasil disimpan')
            ->success()
            ->send();

        return redirect()->to(route('filament.admin.resources.surat-masuks.index'));
    }
}
