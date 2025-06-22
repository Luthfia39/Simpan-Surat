<?php

namespace App\Filament\Resources\SuratMasukResource\Pages;

use App\Filament\Resources\SuratMasukResource;
use App\Models\Surat;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Poll;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;

use App\Filament\Pages\ReviewOCR;
use Illuminate\Support\HtmlString;

class CreateSuratMasuk extends CreateRecord
{
    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.create';

    public $file_path;

    public ?array $data = [];

    public bool $isLoading = false;

    public string $taskId = '';

    public bool $shouldPoll = false;

    public function getHeading(): string
    {
        return "Tambah Surat Masuk";
    }

    public function getSubheading(): string
    {
        return "Unggah dokumen surat masuk Anda, agar dapat terarsipkan dengan rapi dan mudah ditelusuri.";
    }

    public function mount(): void
    {
        parent::mount();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file_path')
                    ->label('Unggah File Surat')
                    ->acceptedFileTypes(['application/pdf'])
                    ->required()
                    ->storeFiles(false)
                    ->maxFiles(1)
                    ->columnSpan(2)
                    ->helperText(new HtmlString('Unggah <b>satu</b> surat resmi <b>berformat UGM</b> dengan tipe <b>file PDF</b>.')),
            ])
            ->model(new Surat())
            ->statePath('data');
    }
    
    protected function submit(): void
    {
        $data = $this->form->getState();

        // Validasi file
        if (empty($data['file_path'])) {
            Notification::make()->title('File harus dipilih.')->danger()->send();
            return;
        }

        $this->file_path = $data['file_path'];

        try {
            $this->dispatch('show-loading');

            // Simpan file ke storage
            $filePath = $data['file_path']->store('suratMasuk', 'public');
            $fileContent = Storage::get($filePath);

            $this->taskId = (string) Str::uuid();
            Storage::put('current_task_id', $this->taskId);

            $pdfUrl = asset('storage/' . $filePath);

            $response = Http::withBody(json_encode(['task_id' => $this->taskId, 'pdf_url' => $pdfUrl]), 'application/json')
                ->post('http://192.168.1.14:3000/submit_pdf');

            // Cek apakah respons berhasil
            if ($response->successful()) {
                Log::info('Data dari Flask:', ['data' => $response->json()]);
                $this->shouldPoll = true;
                Notification::make()->title('File sedang diproses. Mohon tunggu...')->success()->send();
            } else {
                $this->dispatch('hide-loading');
                Notification::make()
                    ->title('Maaf, terjadi error saat memproses file di server OCR.')
                    ->danger()
                    ->send();
                Log::error('Error dari Flask:', ['status' => $response->status(), 'response' => $response->body()]);
            }
        } catch (\Exception $e) {
            $this->dispatch('hide-loading');
            Notification::make()
                ->title('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
            Log::error('Kesalahan proses OCR:', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    #[Poll(seconds: 2)]
    public function pollForOCRResult()
    {
        Log::info(['Polling for OCR result...' => $this->shouldPoll]);
        // if (!$this->taskId) return;
        if (!$this->shouldPoll || !$this->taskId) return;

        $surats = Surat::where('task_id', (string) $this->taskId)->get();
        Log::info('Surat dari MongoDB:', ['surat' => $surats, 'taskId' => $this->taskId]);

        // Cek jika semua surat untuk taskId ini sudah ada dan memiliki ocr_text/extracted_fields
        if ($surats->isNotEmpty() && $surats->every(fn ($surat) => !empty($surat->ocr_text) && !empty($surat->extracted_fields))) {
            $this->shouldPoll = false;
            Log::info('Semua surat untuk Task ID ditemukan dan diproses.', ['taskId' => $this->taskId, 'count' => $surats->count()]);
            $this->dispatch('hide-loading');

            $isUgmLetterDetected = true; 
            foreach ($surats as $surat) {
                if (($surat->is_ugm ?? false) === false) {
                    $isUgmLetterDetected = false;
                    break; 
                }
            }

            if ($isUgmLetterDetected) {
                foreach ($surats as $surat) {
                    if (empty($surat->review_status)) { 
                        $surat->review_status = 'pending_review';
                        $surat->saveQuietly();
                    }
                }
                Notification::make()->title('Proses OCR Selesai!')->body('Dokumen berhasil diproses dan siap di-review.')->success()->send();
                $this->redirect(SuratMasukResource::getUrl('index')); 
            } else {
                // === PERUBAHAN DI SINI: Panggil dispatch untuk modal kustom ===
                $this->dispatch('show-custom-info-modal', [
                    'title' => 'Validasi Dokumen Gagal',
                    'description' => 'Dokumen yang Anda unggah tidak terdeteksi sebagai surat dengan format UGM. Mohon unggah dokumen sesuai permintaan yang telah ditentukan.',
                    'details' => 'Pastikan dokumen adalah surat resmi dengan format UGM yang jelas.', // Bisa tambah detail
                ]);
                // ==========================================================

                Log::warning('Dokumen bukan surat UGM terdeteksi.', ['taskId' => $this->taskId]);
                // Tidak ada redirect, tetap di halaman ini
            }
        } else {
            Log::info('Polling: Belum semua surat untuk Task ID ditemukan atau diproses.', ['taskId' => $this->taskId, 'count_found' => $surats->count()]);
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Simpan Surat')
                ->action(function () {
                    $result = $this->submit();
                }),
        ];
    }
}
