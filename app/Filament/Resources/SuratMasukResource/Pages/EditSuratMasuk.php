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
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Filament\Actions\Action;
use Livewire\Attributes\On;

use Illuminate\Database\Eloquent\Model;

class EditSuratMasuk extends EditRecord
{
    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.edit';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public ?string $taskId = '';

    public $annotations;

    public string $ocr = '';

    public function getHeading(): string
    {
        return "Hasil OCR";
    }

    public function getSubheading(): string
    {
        return "Cek kembali hasil OCR berikut ini, pastikan data yang disimpan telah sesuai.";
    }

    use InteractsWithFormActions;

    protected function resolveRecord(string|int $key): Model
    {
        $record = Surat::where('task_id', $key)->first();

        if (!$record) {
            abort(404, 'Data tidak ditemukan');
        }

        return $record;
    }

    public function mount($record): void
    {
        $this->taskId = $record; // atau ambil dari route

        parent::mount($record);

        $ocrData = $this->record;

        // $ocrData = Surat::where('task_id', $this->taskId)->first();

        Log::info('Surat dari yang mau diedit:', [
            'surat' => $ocrData['ocr_text'],
            'taskId' => $this->taskId
        ]);

        $this->ocr = $ocrData['ocr_text'] ?? '';

        $this->dispatch('ocr-loaded', [
            'ocr' => $ocrData['ocr_text'],
            'extracted_fields' => $ocrData['extracted_fields'],
        ]);

        $this->form->fill([
            'pdf_path' => $ocrData['pdf_url'],
            'ocr_text' => $this->ocr,
            'letter_type' => $ocrData['letter_type'],
        ]);
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Hidden::make('pdf_path'),
                \Filament\Forms\Components\Hidden::make('ocr_text'),
                // \Filament\Forms\Components\Hidden::make('extracted_fields')->default([]),
                \Filament\Forms\Components\Select::make('letter_type')->label('Jenis Surat')->options([
                    'Surat Pernyataan' => 'Surat Pernyataan',
                    'Surat Keterangan' => 'Surat Keterangan',
                    'Surat Tugas' => 'Surat Tugas',
                    'Surat Rekomendasi Beasiswa' => 'Surat Rekomendasi Beasiswa',
                ]),
                // \Filament\Forms\Components\TextInput::make('nomor_surat')->label('Nomor Surat'),
                // \Filament\Forms\Components\TextInput::make('pengirim')->label('Pengirim'),
                // \Filament\Forms\Components\TextInput::make('penandatangan')->label('Penanda Tangan'),
            ])
            ->statePath('data');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->action(function () {
                    $this->onProcessSave();
                }),
        ];
    }

    #[On('data-ready')]
    public function updateData($ocr_final, $annotations)
    {
        $this->ocr= $ocr_final;
        $this->annotations= $annotations;
        $this->save();
    }

    public function onProcessSave() {
        $this->dispatch('update-data');
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $data = $this->form->getState();

        $grouped = [];

        foreach ($this->annotations as $annotation) {
            $type = key($annotation);
            $text = $annotation[$type];

            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }

            $grouped[$type][] = $text;
        }

        // Simpan ke MongoDB
        $surat = Surat::where('task_id', $this->taskId)->firstOrNew();

        $surat->fill([
            'ocr_text' => $this->ocr,
            'letter_type' => $data['letter_type'],
            'extracted_fields' => $grouped,
            'pdf_url' => $data['pdf_path'],
        ]);

        $surat->save();

        Notification::make()
            ->title('Surat berhasil disimpan')
            ->success()
            ->send();

        // Jika ingin redirect, aktifkan bagian ini
        if ($shouldRedirect) {
            $this->redirect(
                route('filament.admin.resources.surat-masuks.index')
            );
        }
    }
}
