<?php

namespace App\Filament\Resources\SuratMasukResource\Pages;

use App\Filament\Resources\SuratMasukResource;

use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CreateSuratMasuk extends Page
{
    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.create';

    public $file_path;

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
            $response = Http::attach(
                'file',
                $fileContent,
                $data['file_path']->getClientOriginalName()
            )->post('http://localhost:5000/process-pdf');

            // Periksa apakah respons berhasil
            if ($response->successful()) {
                $responseData = $response->json();

                // Simpan respons Flask dan path file ke sesi
                Session::put('flask_response', $responseData);
                Session::put('uploaded_file_path', $filePath);

                return [
                    'status' => 'success',
                    'message' => 'File berhasil diproses.',
                    'redirect' => route('filament.admin.resources.surats.edit-response'),
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Gagal memproses file PDF di API Flask.',
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

                    // Tampilkan notifikasi berdasarkan status
                    if ($result['status'] === 'success') {
                        Notification::make()
                            ->title($result['message'])
                            ->success()
                            ->send();

                        // Redirect jika ada URL
                        if (isset($result['redirect'])) {
                            return redirect($result['redirect']);
                        }
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

