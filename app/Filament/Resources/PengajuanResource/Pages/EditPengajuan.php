<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Pengajuan;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class EditPengajuan extends EditRecord
{
    protected static string $resource = PengajuanResource::class;

    protected ?string $heading = "Review Pengajuan";

    protected ?string $subheading = "Cek pengajuan mahasiswa dan ubah statusnya";

    protected function mutateFormDataBeforeSave(array $data): array 
    {
        $record = $this->getRecord();
        $uploadedFilePath = $data['pdf_url'] ; 
        $keterangan = $data['keterangan_final'];
        $newStatus = $data['status']; 

        if ($record->suratKeluar) { 
            $suratKeluarModel = $record->suratKeluar;

            if ($newStatus === 'selesai') {
                if (!empty($uploadedFilePath)) {
                    if ($suratKeluarModel->pdf_url) {
                        Storage::disk('public')->delete('surat_keluar/' . $suratKeluarModel->pdf_url);
                    }
                    $originalFileName = pathinfo($uploadedFilePath, PATHINFO_FILENAME);
                    $fileExtension = pathinfo($uploadedFilePath, PATHINFO_EXTENSION);
                    $newSignedFileName = $originalFileName . '_signed.' . $fileExtension;
                    Storage::disk('public')->move($uploadedFilePath, 'surat_keluar/' . $newSignedFileName);

                    $suratKeluarModel->pdf_url = $newSignedFileName; 
                    $suratKeluarModel->is_show = true; 
                } else {
                    $suratKeluarModel->pengajuan->keterangan = $keterangan;
                    $suratKeluarModel->pengajuan->save();
                    $suratKeluarModel->is_show = false; 
                }
            } else {}
            $suratKeluarModel->save(); 
        } else { }
        
        if (isset($data['suratKeluar'])) {
            unset($data['suratKeluar']);
        }
        return $data; 
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateSuratKeluar')
                ->label('Generate Surat Keluar')
                ->icon('heroicon-s-document-arrow-up')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generate Surat Keluar Draf?') 
                ->modalDescription('Ini akan membuat draf dokumen surat keluar dan memperbarui status pengajuan menjadi "Menunggu Tanda Tangan".')
                ->modalSubmitActionLabel('Konfirmasi Generate Draf') 
                ->action(function (Pengajuan $record) {
                    try {
                        $userData = $record->user;
                        $templateData = $record->template;
                        $dataSurat = $record->data_surat; 

                        if (!$userData || !$templateData) {
                            Notification::make()
                                ->title('Error')
                                ->body('Pengajuan tidak memiliki pengguna atau jenis surat yang valid.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $nomorSurat = 'NO.'. ($dataSurat['nomor_surat']  ?? 'AUTO') . '/UN1/SV.2-TEDI/AKM/PJ/'. date("Y") ;
                        $prodiUser = $userData->major['kode'] ?? 'N/A';
                        $pdfFileName = 'surat_keluar_' . Str::slug($templateData->name) . '_' . Str::slug($dataSurat['nama'] ?? 'unknown') . '_' . time() . '.pdf';
                        $pdfFilePathInStorage = 'surat_keluar/' . $pdfFileName; 

                        try {
                            $viewData = [
                                'pengajuan' => $record,
                                'user' => $userData,
                                'template' => $templateData,
                                'linkFiles' => $dataSurat['link_files'] ?? []
                            ];

                            foreach ($dataSurat as $key => $value) {
                                if (!in_array($key, ['link_files'])) {
                                    $viewData[$key] = $value;
                                }
                            }
                            $viewData['prodi'] = $dataSurat['prodi'] ?? $prodiUser;

                            $pdfContent = view('templates.' . $templateData->class_name, $viewData)->render();
                            Storage::disk('public')->put($pdfFilePathInStorage, Pdf::loadHTML($pdfContent)->output());

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Membuat PDF')
                                ->body('Terjadi kesalahan saat merender PDF: ' . $e->getMessage())
                                ->danger()
                                ->send();
                            return;
                        }
                        $suratKeluar = \App\Models\SuratKeluar::firstOrNew([
                            'pengajuan_id' => $record->_id, 
                        ]);

                        if ($suratKeluar->exists && $suratKeluar->pdf_url) {
                            Storage::disk('public')->delete('surat_keluar/' . $suratKeluar->pdf_url);
                            Storage::disk('public')->delete('surat_keluar_final/' . $suratKeluar->pdf_url);
                            \Log::info('File PDF lama dihapus:', ['file' => $suratKeluar->pdf_url]);
                        }
                        $suratKeluar->fill([
                            'nomor_surat' => $nomorSurat,
                            'prodi' => $prodiUser,
                            'pdf_url' => $pdfFileName, 
                            'template_id' => $templateData->_id,
                            'metadata' => $dataSurat, 
                            'is_show' => false
                        ]);

                        $suratKeluar->save(); 

                        $updateData = [
                            'surat_keluar_id' => $suratKeluar->_id, 
                        ];

                        if ($record->status !== 'selesai' && $record->status !== 'ditolak') {
                            $updateData['status'] = 'menunggu_ttd';
                        }
                        $record->update($updateData); 

                        Notification::make()
                            ->title('Draf Surat Keluar Berhasil Dibuat') 
                            ->body('Nomor Surat: ' . $nomorSurat . ' telah dibuat. 
                                Status pengajuan diubah menjadi "Menunggu Tanda Tangan". <a href="' 
                                . asset('storage/' . $pdfFilePathInStorage) . '" 
                                target="_blank" class="underline">Lihat Draf PDF</a>') 
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.pengajuans.edit', ['record' => $record->getKey()]);

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Membuat Surat Keluar')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(function (Pengajuan $record): bool {
                    $hasSuratKeluar = $record->suratKeluar()->exists();
                    $hasFinalPdf = $hasSuratKeluar && !empty($record->suratKeluar->pdf_url);

                    return auth()->user()->is_admin &&
                           in_array($record->status, ['diproses', 'menunggu_ttd']) &&
                           !$hasFinalPdf; 
                }),
        ];
    }

    /**
     * Mengatur aksi tombol di bagian bawah form.
     * Mengubah label tombol 'save' dan 'cancel'.
     */
    protected function getFormActions(): array
    {
        // Panggil metode parent untuk mendapatkan aksi default
        $actions = parent::getFormActions();

        // Iterasi dan modifikasi aksi
        $modifiedActions = array_map(function (Actions\Action $action) {
            switch ($action->getName()) {
                case 'save':
                    // Ubah label tombol 'Save changes' menjadi 'Simpan'
                    return $action->label('Simpan');
                case 'cancel':
                    // Ubah label tombol 'Cancel' menjadi 'Batal'
                    return $action->label('Batal');
            }
            return $action;
        }, $actions);

        return $modifiedActions;
    }
}
