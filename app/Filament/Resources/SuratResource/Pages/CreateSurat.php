<?php

namespace App\Filament\Resources\SuratResource\Pages;

use App\Filament\Resources\SuratResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms;
use App\Models\Surat;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class CreateSurat extends Page
{
    protected static string $resource = SuratResource::class;

    protected static string $view = 'filament.resources.surat-resource.pages.create-surat';

    public function formSchema(): array
    {
        return [
            // Forms\Components\FileUpload::make('file_path')
            //     ->label('Unggah File PDF')
            //     ->acceptedFileTypes(['application/pdf'])
            //     ->maxFiles(1)
            //     ->directory('surat')
            //     ->required(),
        ];
    }

    public function processFile(array $data)
    {
        // // Simpan file sementara untuk dikirim ke API Flask
        // $filePath = $data['file_path'];
        // $fileContent = Storage::get($filePath);

        // // Kirim file ke API Flask
        // $response = Http::attach(
        //     'file',
        //     $fileContent,
        //     basename($filePath)
        // )->post('http://localhost:5000/process-pdf');

        // if ($response->successful()) {
        //     $responseData = $response->json();

        //     // Simpan respons Flask ke sesi
        //     Session::put('flask_response', $responseData);
        //     Session::put('uploaded_file_path', $filePath);

        //     // Redirect ke halaman edit respons
        //     return redirect()->route('filament.admin.resources.surats.edit-response');
        // } else {
        //     throw new \Exception('Gagal memproses file PDF di API Flask.');
        // }
    }
}
