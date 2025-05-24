<?php

namespace App\Filament\Resources\SuratMasukResource\Pages;

use App\Filament\Resources\SuratMasukResource;
use App\Models\Surat;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Poll;
use Filament\Resources\Pages\CreateRecord;

class CreateSuratMasuk extends CreateRecord
{
    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.create';

    public $file_path;

    public ?array $data = [];

    public bool $isLoading = false;

    public string $taskId = '';

    public bool $shouldPoll = false;

    public static function shouldRegisterInNavigation(): bool
    {
        return true; 
    }

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
        $this->form->fill();
    }
    
    protected function submit(): array
    {
        $data = $this->form->getState();

        // Validasi file
        if (!$data['file_path']) {
            return [
                'status' => 'error',
                'message' => 'File harus dipilih.',
            ];
        }

        try {
            $this->dispatch('show-loading');

            // Simpan file ke storage
            $filePath = $data['file_path']->store('suratMasuk', 'public');
            $fileContent = Storage::get($filePath);

            $this->taskId = (string) Str::uuid();
            Storage::put('current_task_id', $this->taskId);

            $pdfUrl = asset('storage/' . $filePath);

            $response = Http::withBody(json_encode(['task_id' => $this->taskId, 'pdf_url' => $pdfUrl]), 'application/json')
                ->post('http://172.20.0.202:3000/submit_pdf');

            // Cek apakah respons berhasil
            if ($response->successful()) {
                Log::info('Data dari Flask:', ['data' => $response->json()]);
                $this->shouldPoll = true;
                return [
                    'status' => 'success',
                    'message' => 'File sedang diproses.',
                    'redirect' => route('filament.admin.resources.surat-masuks.edit', ['taskId' => $this->taskId]),
                ];
            } else {
                $this->dispatch('hide-loading');
                Notification::make()
                    ->title('Maaf, terjadi error saat memproses file di server.')
                    ->danger()
                    ->send();
                return [
                    'status' => 'error',
                    'message' => 'Maaf, terjadi error saat memproses file di server.',
                ];
            }
        } catch (\Exception $e) {
            $this->dispatch('hide-loading');
            Notification::make()
                ->title('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ];
        }
    }

    #[Poll(seconds: 2)]
    public function pollForOCRResult()
    {
        Log::info(['Polling for OCR result...' => $this->shouldPoll]);
        // if (!$this->taskId) return;
        if (!$this->shouldPoll || !$this->taskId) return;

        $surat = Surat::where('task_id', (string) $this->taskId)->first();
        Log::info('Surat dari MongoDB:', ['surat' => $surat, 'taskId' => $this->taskId]);

        if ($surat) {
            Log::info('Surat ditemukan');
            $this->dispatch('hide-loading');
            $this->ocrData = json_decode($surat->ocr_text, true);

            // Redirect langsung menggunakan Livewire
            $this->redirect(
                route('filament.admin.resources.surat-masuks.edit', ['record' => $surat->task_id])
            );
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
