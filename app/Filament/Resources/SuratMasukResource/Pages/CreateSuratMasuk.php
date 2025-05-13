<?php

namespace App\Filament\Resources\SuratMasukResource\Pages;

use App\Filament\Resources\SuratMasukResource;
use App\Models\Surat;
use Filament\Resources\Pages\Page;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CreateSuratMasuk extends Page
{
    use InteractsWithFormActions;

    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.create';

    public $file_path;

    public ?array $data = [];

    public bool $isLoading = false;

    public function getTitle(): string
    {
        return 'Input Surat';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('file_path')
                ->label('Unggah File Surat')
                ->acceptedFileTypes(['application/pdf'])
                ->required()
                ->storeFiles(false),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
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
            $filePath = $data['file_path']->store('surat', 'public');
            $fileContent = Storage::get($filePath);

            $taskId = (string) Str::uuid();
            Storage::put('current_task_id', $taskId);

            $pdfUrl = asset('storage/' . $filePath);

            $response = Http::withBody(json_encode(['task_id' => $taskId, 'pdf_url' => $pdfUrl]), 'application/json')
                ->post('http://192.168.1.77:3000/submit_pdf');

            // Cek apakah respons berhasil
            if ($response->successful()) {

                Log::info('Data dari Flask:', ['data' => $response->json()]);

                $surat = Surat::where('task_id', $taskId)->first();

                if ($surat) {
                    $this->dispatch('hide-loading');
                    return [
                        'status' => 'success',
                        'message' => 'File sedang diproses.',
                        'redirect' => route('filament.admin.resources.surat-masuks.edit', ['taskId' => $taskId]),
                    ];
                } else {
                    return [
                        'status' => 'waiting',
                    ];
                }
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Maaf, terjadi error saat memproses file di server.',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ];
        }
    }


    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Simpan Surat')
                ->action(function () {
                    $result = $this->submit();

                    if ($result['status'] === 'success') {
                        Notification::make()
                            ->title($result['message'])
                            ->success()
                            ->send();

                        return redirect($result['redirect']);
                    } elseif ($result['status'] === 'waiting') {
                    } else {
                        Notification::make()
                            ->title($result['message'])
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
