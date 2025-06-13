<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Models\Surat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Forms\Form; 

class ReviewOCR extends Page
{
    protected static string $view = 'filament.pages.review-o-c-r';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Review Hasil OCR';
    protected static ?string $slug = 'review-ocr-results/{taskId}';
    protected static bool $shouldRegisterNavigation = false;

    use InteractsWithForms;
    use InteractsWithActions;

    // Public properties
    public string $taskId;
    public Collection $foundLetters;
    public array $documentsData = [];
    public string $ocr = '';
    public $annotations = [];
    public $letter_type;
    public ?int $selectedDocumentIndexForViewer = null;

    // --- Wizard Specific Properties ---
    public ?int $currentWizardStepIndex = 0;
    public ?Surat $currentDocumentModel = null;

    public function getHeading(): string { return "Hasil OCR"; }
    public function getSubheading(): string { return "Cek kembali hasil OCR berikut ini, pastikan data yang disimpan telah sesuai."; }

    public function mount(?string $taskId = null): void
    {
        if (is_null($taskId)) { abort(404, 'Task ID tidak ditemukan.'); }
        $this->taskId = $taskId;

        $this->foundLetters = Surat::where('task_id', $this->taskId)
                                    ->orderBy('document_index')
                                    ->get();

        if ($this->foundLetters->isEmpty()) { abort(404, 'Tidak ada dokumen ditemukan untuk Task ID ini.'); }

        foreach ($this->foundLetters as $letter) {
            $this->documentsData[$letter->document_index] = [
                'letter_type' => $letter->letter_type,
            ];
        }

        $this->currentWizardStepIndex = 0;
        $this->loadDocumentForWizardStep();
    }

    public function loadDocumentForWizardStep(): void
    {
        $documentToLoad = $this->foundLetters->get($this->currentWizardStepIndex);

        if (!$documentToLoad) {
            Notification::make()->title('Gagal memuat dokumen untuk langkah wizard.')->danger()->send();
            return;
        }

        $this->currentDocumentModel = $documentToLoad;
        $this->selectedDocumentIndexForViewer = $documentToLoad->document_index;

        $this->ocr = $documentToLoad->ocr_text ?? '';
        $this->annotations = json_decode($documentToLoad->extracted_fields ?? '[]', true);

        $this->dispatch('ocr-loaded', [
            'ocr' => $this->ocr,
            'extracted_fields' => $this->annotations,
        ]);

        $this->getForm('wizardForm')->fill($this->documentsData[$this->selectedDocumentIndexForViewer]);

        Notification::make()->title('Memuat Dokumen ' . $this->selectedDocumentIndexForViewer)->success()->send();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('letter_type')
                ->label('Jenis Surat')
                ->options([
                    'Surat Pernyataan' => 'Surat Pernyataan',
                    'Surat Keterangan' => 'Surat Keterangan',
                    'Surat Tugas' => 'Surat Tugas',
                    'Surat Rekomendasi Beasiswa' => 'Surat Rekomendasi Beasiswa',
                ])
                ->required()
                ->default(fn (?Model $record) => $record?->letter_type)
                ->live(),
        ];
    }

    protected function getForms(): array
    {
        return [
            'wizardForm' => $this->makeForm()
                ->schema([
                    Wizard::make($this->getWizardSteps())
                        ->startOnStep($this->currentWizardStepIndex)
                        ->skippable(false)
                        ->submitAction(
                            Action::make('submit')
                                ->label(fn (Wizard $wizard) => $this->isLastWizardStep() ? 'Selesai & Simpan Semua' : 'Simpan & Lanjutkan')
                                ->action(function () {
                                    $this->saveCurrentDocumentAndAdvance();
                                })
                        )
                        ->extraAttributes([
                            'wire:ignore.self',
                            'x-on:step-activated.stop' => 'Livewire.dispatch(\'wizard-step-changed\', {stepIndex: $event.detail.step});',
                        ]),
                ])
                ->statePath('documentsData.' . ($this->selectedDocumentIndexForViewer ?? 'default')),
        ];
    }

    protected function getWizardSteps(): array
    {
        $steps = [];
        foreach ($this->foundLetters as $index => $letter) {
            $steps[] = Step::make('Dokumen ' . $letter->document_index)
                ->description($letter->letter_type ?? 'Tidak Diketahui')
                ->schema([
                    ...$this->getFormSchema(),
                ]);
        }
        return $steps;
    }

    public function isLastWizardStep(): bool
    {
        return $this->currentWizardStepIndex === $this->foundLetters->count() - 1;
    }

    #[On('wizard-step-changed')]
    public function onWizardStepChanged(array $eventData): void
    {
        $newStepIndex = $eventData['stepIndex'];
        $this->currentWizardStepIndex = $newStepIndex;
        $this->loadDocumentForWizardStep();
    }

    public function saveCurrentDocumentAndAdvance(): void
    {
        $currentDocument = $this->currentDocumentModel;

        if (!$currentDocument) {
            Notification::make()->title('Dokumen tidak ditemukan untuk disimpan.')->danger()->send();
            return;
        }

        try {
            $this->getForm('wizardForm')->validate();
            $formData = $this->getForm('wizardForm')->getState();

            $currentDocument->fill([
                'letter_type' => $formData['letter_type'],
            ]);

            $currentDocument->extracted_fields = json_encode($currentDocument->extracted_fields);

            $currentDocument->save();
            Notification::make()->title('Dokumen ' . $currentDocument->document_index . ' berhasil disimpan.')->success()->send();

            if ($this->isLastWizardStep()) {
                $this->redirect(route('filament.admin.resources.surat-masuks.index'));
            }

        } catch (\Throwable $e) {
            Log::error("Error saving current document {$currentDocument->id}: " . $e->getMessage());
            Notification::make()->title('Gagal menyimpan dokumen ' . $currentDocument->document_index . '.')->danger()->send();
        }
    }

    public function loadDocumentForViewer(int $documentIndex): void
    {
        $document = $this->foundLetters->firstWhere('document_index', $documentIndex);
        if (!$document) { /* ... */ return; }

        $this->ocr = $document->ocr_text ?? '';
        $this->annotations = json_decode($document->extracted_fields ?? '[]', true);
        $this->selectedDocumentIndexForViewer = $document->document_index;

        $this->dispatch('ocr-loaded', [
            'ocr' => $this->ocr,
            'extracted_fields' => $this->annotations,
        ]);
        Notification::make()->title('Menampilkan Dokumen ' . $this->selectedDocumentIndexForViewer)->success()->send();
    }

    #[On('data-ready')]
    public function updateDocumentOcrAndAnnotations(int $documentIndex, string $ocr_final, array $annotations_final): void
    {
        $suratToUpdate = $this->foundLetters->firstWhere('document_index', $documentIndex);
        if ($suratToUpdate) {
            $suratToUpdate->ocr_text = $ocr_final;
            $suratToUpdate->extracted_fields = $annotations_final;
            Notification::make()->title('Perubahan untuk Dokumen ' . $documentIndex . ' disiapkan.')->success()->send();
        } else {
             Notification::make()->title('Gagal menemukan dokumen untuk diperbarui.')->danger()->send();
        }
    }
}