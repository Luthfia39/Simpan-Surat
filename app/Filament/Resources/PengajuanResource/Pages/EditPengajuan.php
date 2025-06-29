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

    protected function getHeaderActions(): array
    {
        return [
            // Aksi Generate Surat Keluar, hanya terlihat jika kondisi terpenuhi
            Actions\Action::make('generateSuratKeluar')
                ->label('Generate Surat Keluar')
                ->icon('heroicon-s-document-arrow-up')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generate Surat Keluar?')
                ->modalDescription('Ini akan membuat dokumen surat keluar dan memperbarui status pengajuan menjadi selesai jika belum.')
                ->modalSubmitActionLabel('Konfirmasi Generate')
                ->action(function (Pengajuan $record) {
                    try {
                        $userData = $record->user;
                        $templateData = $record->template;
                        $dataSurat = $record->data_surat; 

                        if (!$userData || !$templateData) {
                            Notification::make()
                                ->title('Error')
                                ->body('Pengajuan tidak memiliki pengguna atau template yang valid.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $nomorSurat = 'NO.'. ($dataSurat['nomor_surat']  ?? 'AUTO') . '/UN1/SV2-TEDI/AKM/PJ/'. date("Y") ;
                        $prodiUser = $userData->major['kode'] ?? 'N/A';

                        $pdfFileName = 'surat_keluar_' . Str::slug($templateData->name) . '_' . Str::slug($dataSurat['nama'] ?? 'unknown') . '_' . time() . '.pdf';
                        $pdfFilePathInStorage = 'surat_keluar/' . $pdfFileName; // Path relatif di storage
                        
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
                            'pengajuan_id' => $record->_id, // Cari berdasarkan pengajuan_id
                        ]);

                        // Jika record_id sudah ada (berarti record ditemukan), hapus file PDF lama
                        if ($suratKeluar->exists && $suratKeluar->pdf_url) {
                            Storage::disk('public')->delete('surat_keluar/' . $suratKeluar->pdf_url);
                            \Log::info('File PDF lama dihapus:', ['file' => $suratKeluar->pdf_url]);
                        }

                        // Isi properti model dengan data terbaru (akan di-update atau di-create)
                        $suratKeluar->fill([
                            'nomor_surat' => $nomorSurat,
                            'prodi' => $prodiUser,
                            'pdf_url' => $pdfFileName, // Simpan nama file PDF yang baru
                            'template_id' => $templateData->_id,
                            'metadata' => $dataSurat
                        ]);

                        $suratKeluar->save(); 

                        // Perbarui Pengajuan dengan ID Surat Keluar dan Status (jika belum selesai)
                        $updateData = [
                            'surat_keluar_id' => $suratKeluar->_id, // Akan menunjuk ke _id SuratKeluar yang di-update/dibuat
                        ];

                        // if ($record->status !== 'selesai' and $record->template->name !== 'Permohonan Dosen Pengampu (SV)' && $record->template->name !== 'Permohonan Dosen Pengampu (Non-SV)') {
                        //     $updateData['status'] = 'selesai';
                        // }
                        $record->update($updateData);

                        Notification::make()
                            ->title('Surat Keluar Berhasil Dibuat/Diperbarui')
                            ->body('Nomor Surat: ' . $nomorSurat . ' telah dibuat. <a href="' . asset('storage/' . $pdfFilePathInStorage) . '" target="_blank" class="underline">Lihat PDF</a>')
                            ->success()
                            ->send();

                        // Redirect kembali ke halaman pengajuan yang sedang diedit
                        return redirect()->route('filament.admin.resources.pengajuans.edit', ['record' => $record->getKey()]);

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Membuat Surat Keluar')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn (Pengajuan $record): bool =>
                    auth()->user()->is_admin &&
                    $record->status === 'diproses'
                ),
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
