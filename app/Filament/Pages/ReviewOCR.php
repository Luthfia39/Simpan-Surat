<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms; // For using forms
use Filament\Actions\Concerns\InteractsWithActions; // For using Filament actions (like your save button)
use Filament\Notifications\Notification; // For showing notifications
use Filament\Pages\Page; // Base class for custom pages
use App\Models\Surat; // Your Surat model
use Illuminate\Support\Collection; // For working with collections of models
use Illuminate\Support\Facades\Log; // For logging
use Livewire\Attributes\On; // For Livewire event listeners
use Filament\Forms\Components\Select; // Filament form components
use Filament\Forms\Components\TextInput; // Filament form components
use Illuminate\Database\Eloquent\Model; // For type hinting

class ReviewOCR extends Page
{
    // Define the Blade view for this page
    protected static string $view = 'filament.pages.review-o-c-r';

    // Optional: Navigation icon and title
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Review Hasil OCR';

    // Traits for Filament functionalities
    use InteractsWithForms;
    use InteractsWithActions;

    // Public properties to hold data for the Livewire component
    public string $taskId; // This will receive the task_id from the URL
    public Collection $foundLetters; // Collection of all Surat models for this task_id

    // This array will hold the form data for all documents.
    // The keys will be document_index, and values will be arrays of form field data.
    public array $documentsData = [];

    // Properties to control the OCR viewer (which document is currently displayed)
    public string $ocr = '';
    public $annotations = []; // This will be the extracted fields for the currently viewed document
    public $letter_type;
    public ?int $selectedDocumentIndexForViewer = null; // Index of the document currently shown in the OCR viewer

    /**
     * Define the route parameter for this page.
     * This makes the page accessible via /admin/review-ocr-results/{taskId}
     */
    protected static ?string $slug = 'review-ocr-results/{taskId}';

    protected static bool $shouldRegisterNavigation = false;

    /**
     * This method is called when the Livewire component is initialized.
     * It fetches all relevant documents based on the taskId.
     *
     * @param string|null $taskId The task_id from the URL.
     * @return void
     */
    public function mount(?string $taskId = null): void
    {
        if (is_null($taskId)) {
            abort(404, 'Task ID tidak ditemukan.');
        }

        $this->taskId = $taskId;

        // Fetch all Surat records associated with this taskId
        $this->foundLetters = Surat::where('task_id', $this->taskId)
                                    ->orderBy('document_index')
                                    ->get();

        if ($this->foundLetters->isEmpty()) {
            abort(404, 'Tidak ada dokumen ditemukan untuk Task ID ini.');
        }

        // Initialize $documentsData array for all documents.
        // Each key will be the document_index, and value will be an array of form fields.
        foreach ($this->foundLetters as $letter) {
            $this->documentsData[$letter->document_index] = [
                'letter_type' => $letter->letter_type,
                // Add other manual fields here from your Surat model if they exist
                // e.g., 'nomor_surat' => $letter->nomor_surat,
                // For extracted_fields, they are typically dynamic, so we'll handle them
                // via the annotations directly from the OCR viewer's updates.
            ];
        }

        // dd($this->documentsData);

        // Set the first document as the one to be displayed initially in the OCR viewer
        $firstDocument = $this->foundLetters->first();
        if ($firstDocument) {
            $this->letter_type = $firstDocument->letter_type;
            $this->selectedDocumentIndexForViewer = $firstDocument->document_index;
            $this->loadDocumentForViewer($firstDocument);
        }
    }

    /**
     * Defines the schema for the form components used for each document.
     * This method will be called dynamically for each document.
     *
     * @return array<mixed>
     */
    protected function getFormSchema(): array
    {
        return [
            // \Filament\Forms\Components\Hidden::make('pdf_path'),
            // \Filament\Forms\Components\Hidden::make('ocr_text'),
            \Filament\Forms\Components\Select::make('letter_type')->label('Jenis Surat')->options([
                'Surat Pernyataan' => 'Surat Pernyataan',
                'Surat Keterangan' => 'Surat Keterangan',
                'Surat Tugas' => 'Surat Tugas',
                'Surat Rekomendasi Beasiswa' => 'Surat Rekomendasi Beasiswa',
            ])
            ->required()
            ->default(fn (?Model $record) => $record?->letter_type),
        ];
    }

    /**
     * Action for the main save button.
     *
     * @return array<\Filament\Actions\Action>
     */
    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('saveAll')
                ->label('Simpan Semua Dokumen')
                ->action(function () {
                    $this->saveAllDocuments();
                })
                ->button()
                ->color('primary')
                ->icon('heroicon-o-check-circle'),
        ];
    }

    /**
     * Loads the OCR text and annotations for a specific document into the viewer.
     * This method is called when the user selects a document from the viewer dropdown.
     *
     * @param Surat $document The Surat model instance to display.
     * @return void
     */
    public function loadDocumentForViewer(Surat $document): void
    {
        $this->ocr = $document->ocr_text ?? '';
        // Ensure extracted_fields is decoded from JSON if it's stored as JSON string
        $this->annotations = json_decode($document->extracted_fields ?? '[]', true);
        $this->selectedDocumentIndexForViewer = $document->document_index;

        // Dispatch event to your frontend Blade component for highlighting
        $this->dispatch('ocr-loaded', [
            'ocr' => $this->ocr,
            'extracted_fields' => $this->annotations,
        ]);
    }

    /**
     * Livewire event listener to update OCR text and annotations from the frontend.
     * This is called when the user manually selects/highlights text in the OCR viewer.
     *
     * @param int $documentIndex The document_index of the document being updated.
     * @param string $ocr_final The final OCR text after user edits.
     * @param array $annotations_final The final annotations after user selections.
     * @return void
     */
    #[On('data-ready')] // Ensure your frontend dispatches 'data-ready' with these params
    public function updateDocumentOcrAndAnnotations(int $documentIndex, string $ocr_final, array $annotations_final): void
    {
        // Find the specific Surat model instance in the collection
        $suratToUpdate = $this->foundLetters->firstWhere('document_index', $documentIndex);

        if ($suratToUpdate) {
            // Update the model instance directly in the `$foundLetters` collection
            // These changes will be persisted when `saveAllDocuments` is called.
            $suratToUpdate->ocr_text = $ocr_final;
            $suratToUpdate->extracted_fields = $annotations_final; // Keep as array here for later JSON encoding

            Notification::make()
                ->title('Perubahan untuk Dokumen ' . $documentIndex . ' disiapkan.')
                ->success()
                ->send();
        } else {
             Notification::make()
                ->title('Gagal menemukan dokumen untuk diperbarui.')
                ->danger()
                ->send();
        }
    }

    /**
     * Saves all documents in the `$foundLetters` collection.
     * This method is called by the "Simpan Semua Dokumen" action.
     *
     * @return void
     */
    public function saveAllDocuments(): void
    {
        $savedCount = 0;
        $errorCount = 0;

        // Loop through each Surat model in the collection
        foreach ($this->foundLetters as $letter) {
            try {
                // Fill the model with data from the form component (if it exists)
                // $this->documentsData[$letter->document_index] contains the data from the form fields.
                $formDataForDocument = $this->documentsData[$letter->document_index];

                $letter->fill([
                    'letter_type' => $formDataForDocument['letter_type'],
                    // Fill other manual form fields here from $formDataForDocument
                    // 'nomor_surat' => $formDataForDocument['nomor_surat'],
                ]);

                // ocr_text and extracted_fields are updated via updateDocumentOcrAndAnnotations
                // Ensure extracted_fields is encoded to JSON before saving to DB
                $letter->extracted_fields = json_encode($letter->extracted_fields);

                $letter->save();
                $savedCount++;
            } catch (\Throwable $e) {
                Log::error("Error saving document {$letter->id}: " . $e->getMessage());
                $errorCount++;
            }
        }

        if ($savedCount > 0) {
            Notification::make()
                ->title("Berhasil menyimpan {$savedCount} dokumen.")
                ->success()
                ->send();
        }
        if ($errorCount > 0) {
            Notification::make()
                ->title("Gagal menyimpan {$errorCount} dokumen.")
                ->danger()
                ->send();
        }

        // Redirect after saving all documents
        $this->redirect(route('filament.admin.resources.surat-masuks.index'));
    }
}