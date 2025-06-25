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
            Actions\DeleteAction::make(),
            // Aksi Generate Surat Keluar, hanya terlihat jika kondisi terpenuhi
            Actions\Action::make('generateSuratKeluar')
                ->label('Generate Surat Keluar')
                ->icon('heroicon-s-document-arrow-down')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generate Surat Keluar?')
                ->modalDescription('Ini akan membuat dokumen surat keluar dan memperbarui status pengajuan menjadi selesai jika belum.')
                ->modalSubmitActionLabel('Konfirmasi Generate')
                ->action(function (Pengajuan $record) {
                    try {
                        $userData = $record->user;
                        $templateData = $record->template;
                        $dataSurat = $record->data_surat; // Ini adalah array PHP dari data input template

                        if (!$userData || !$templateData) {
                            Notification::make()
                                ->title('Error')
                                ->body('Pengajuan tidak memiliki pengguna atau template yang valid.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Prioritaskan nomor surat dari admin yang diisi di data_surat
                        // Jika tidak ada, fallback ke generate otomatis
                        $nomorSurat = 'NO.'. $dataSurat['nomor_surat']  . '/UN1/SV2-TEDI/AKM/PJ/'. date("Y") ;
                        $prodiUser = $userData->major['kode'] ?? 'N/A'; // Prodi dari data user

                        $pdfPath = null;
                        try {
                            // --- KUNCI PERUBAHAN DI SINI ---
                            // Bongkar array $dataSurat menjadi variabel individual untuk Blade
                            $viewData = [
                                'pengajuan' => $record,
                                'user' => $userData,
                                'template' => $templateData,
                                'linkFiles' => $dataSurat['link_files'] ?? [] // Link file yang diupload
                            ];

                            // Tambahkan semua isi $dataSurat langsung ke $viewData
                            // Ini akan membuat $nama, $nim, $prodi (dari data_surat), $nama_ortu, dll., tersedia
                            foreach ($dataSurat as $key => $value) {
                                // Hindari menimpa variabel yang sudah ada seperti 'linkFiles'
                                if (!in_array($key, ['link_files'])) {
                                    $viewData[$key] = $value;
                                }
                            }
                            // Pastikan variabel $prodi di Blade sesuai dengan yang di-input admin
                            // Jika $dataSurat punya 'prodi', gunakan itu. Jika tidak, gunakan dari user.
                            $viewData['prodi'] = $dataSurat['prodi'] ?? $prodiUser;


                            $pdfContent = view('templates.' . $templateData->class_name, $viewData)->render();

                            $pdfFileName = 'surat_keluar_' . Str::slug($templateData->name) . '_' . Str::slug($dataSurat['nama'] ?? 'unknown') . '_' . time() . '.pdf';
                            Storage::disk('public')->put('surat_keluar/' . $pdfFileName, Pdf::loadHTML($pdfContent)->output());
                            // $pdfUrl = Storage::disk('public')->url('surat_keluar/' . $pdfFileName);

                        } catch (\Exception $e) {
                             Notification::make()
                                ->title('Gagal Membuat PDF')
                                ->body('Terjadi kesalahan saat merender PDF: ' . $e->getMessage())
                                ->danger()
                                ->send();
                            return;
                        }

                        // Buat record SuratKeluar baru
                        $suratKeluar = \App\Models\SuratKeluar::create([
                            'nomor_surat' => $nomorSurat,
                            'prodi' => $prodiUser, // Ini adalah prodi yang tersimpan di SuratKeluar (dari user)
                            'pdf_url' => $pdfFileName,
                            'template_id' => $templateData->_id,
                            'pengajuan_id' => $record->_id,
                            'metadata' => $dataSurat // Tetap simpan semua data_surat sebagai metadata
                        ]);

                        // Perbarui Pengajuan dengan ID Surat Keluar dan Status (jika belum selesai)
                        $updateData = [
                            'surat_keluar_id' => $suratKeluar->_id,
                        ];
                        if ($record->status !== 'selesai') {
                            $updateData['status'] = 'selesai';
                        }
                        $record->update($updateData);

                        Notification::make()
                            ->title('Surat Keluar Berhasil Dibuat')
                            ->body('Nomor Surat: ' . $nomorSurat . ' telah dibuat. <a href="http://127.0.0.1:8000/storage/surat_keluar/' . $pdfFileName . '" target="_blank" class="underline">Lihat PDF</a>')
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
                // --- Kondisi Visibilitas Tombol Kunci ---
                ->visible(fn (Pengajuan $record): bool =>
                    auth()->user()->is_admin &&          // Hanya admin
                    $record->status === 'diproses' &&     // Hanya jika status 'diproses'
                    !$record->surat_keluar_id             // Dan belum ada surat keluar yang terkait
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
