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

class EditSuratMasuks extends EditRecord
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

    public function mount(string|int $record): void
    {

        parent::mount($record);

        $ocrData = $this->record;
        $this->annotations = is_string($this->record->extracted_fields) ? json_decode($this->record->extracted_fields, true) : ($this->record->extracted_fields ?? []);

        Log::info('Surat dari yang mau diedit:', [
            'surat' => $ocrData['ocr_text'],
            'taskId' => $this->taskId
        ]);

        // $this->ocr = $ocrData['ocr_text'] ?? '';
        $this->ocr = $this->record->ocr_text ?? '';

        $this->dispatch('ocr-loaded', [
            'ocr' => $this->ocr,
            'extracted_fields' => $this->annotations,
        ]);

        $this->form->fill([
            'letter_type' => $ocrData['letter_type'],
        ]);
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Select::make('letter_type')->label('Jenis Surat')->options([
                    'Surat Pernyataan' => 'Surat Pernyataan',
                    'Surat Keterangan' => 'Surat Keterangan',
                    'Surat Tugas' => 'Surat Tugas',
                    'Surat Rekomendasi Beasiswa' => 'Surat Rekomendasi Beasiswa',
                ])
                ->createOptionForm([ 
                    TextInput::make('letter_type') 
                        ->label('Jenis Surat Lainnya')
                        ->required()
                        ->maxLength(255),
                ])
                ->createOptionUsing(function (array $data) { 
                    return $data['letter_type']; 
                })
                ->required(),
            ])
            ->model($this->record)
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
    public function updateData(string $ocr_final, array $annotations)
    {
        $this->record->ocr_text = $ocr_final;
        $this->record->extracted_fields = $annotations;
        
        $this->ocr= $ocr_final;
        $this->annotations= $annotations;
        try {
            $this->record->save(); 
            \Log::info('Livewire: Document saved to database from updateDocumentOcrAndAnnotations.', 
            ['id' => $this->record->id]);
            $this->dispatch('document-update-completed');
            Notification::make()->title('Perubahan berhasil disimpan!')->success()->send();
        } catch (\Exception $e) {
            \Log::error('Livewire: Error saving document from updateDocumentOcrAndAnnotations:', 
            ['error' => $e->getMessage(), 'id' => $this->record->id]);
            Notification::make()->title('Gagal menyimpan perubahan: ' . $e->getMessage())->danger()->send();
        }
    }

    public function onProcessSave(): void {
        $this->form->validate();
        $formData = $this->form->getState(); 

        $this->record->fill([
            'letter_type' => $formData['letter_type'],
        ]);

        $this->record->review_status = 'reviewed'; 
        $this->record->save();
        Notification::make()->title('Review OCR untuk Dokumen ' . 
        $this->record->document_index . ' berhasil disimpan!')->success()->send();
        $this->redirect(SuratMasukResource::getUrl('index'));
    }
}
