<?php

namespace App\Filament\Resources\SuratMasukResource\Pages;

use App\Filament\Resources\SuratMasukResource;

use Filament\Resources\Pages\Page;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Filament\Pages\Concerns\InteractsWithFormActions;

class CreateSuratMasuk extends Page
{
    use InteractsWithFormActions;
    
    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.create';

    public $file_path;

    public ?array $data = [];

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
            // Simpan file ke storage
            $filePath = $data['file_path']->store('surat', 'public');
            $fileContent = Storage::get($filePath);

            // Kirim file ke API Flask
            // $response = Http::attach(
            //     'file',
            //     $fileContent,
            //     $data['file_path']->getClientOriginalName()
            // )->post('http://127.0.0.1:3000/process_pdf');zzzz

            $pdfUrl = asset('storage/' . $filePath); 

            dd(['pdfUrl' => $pdfUrl, 'fileContent' => $fileContent, 'filePath' => $filePath]);

            $response = Http::withBody(json_encode(['pdf_url' => $pdfUrl]), 'application/json')
                ->post('http://127.0.0.1:3000/process_pdf');

            // Cek apakah respons berhasil
            if ($response->successful()) {
                $responseData = $response->json();

                // 🔍 Cek apakah format UGM
                if (!isset($responseData['is_ugm_format']) || $responseData['is_ugm_format'] === false) {
                    return [
                        'status' => 'error',
                        'message' => 'Format surat tidak sesuai template UGM.',
                    ];
                }

                // ✅ Format valid → simpan ke session dan lanjut ke edit
                Session::put('flask_response', $responseData);
                Session::put('uploaded_file_path', $filePath);

                return [
                    'status' => 'success',
                    'message' => 'File berhasil diproses. Lanjut ke input data.',
                    'redirect' => route('filament.admin.resources.surat-masuks.edit'),
                ];
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

