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
            // Aksi Generate Surat Keluar.
            // Tombol ini hanya terlihat jika admin masuk dan status pengajuan adalah 'diproses' atau 'menunggu_ttd',
            // DAN belum ada file PDF final yang diunggah untuk surat keluar terkait.
            Actions\Action::make('generateSuratKeluar')
                ->label('Generate Surat Keluar')
                ->icon('heroicon-s-document-arrow-up')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generate Surat Keluar Draf?') // Ubah judul modal
                ->modalDescription('Ini akan membuat draf dokumen surat keluar dan memperbarui status pengajuan menjadi "Menunggu Tanda Tangan".') // Ubah deskripsi modal
                ->modalSubmitActionLabel('Konfirmasi Generate Draf') // Ubah label tombol submit modal
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

                        // Menentukan nomor surat. Gunakan nomor_surat dari data_surat jika ada, atau 'AUTO'.
                        $nomorSurat = 'NO.'. ($dataSurat['nomor_surat']  ?? 'AUTO') . '/UN1/SV2-TEDI/AKM/PJ/'. date("Y") ;
                        $prodiUser = $userData->major['kode'] ?? 'N/A';

                        // Menentukan nama file PDF dan path penyimpanan untuk draf.
                        $pdfFileName = 'surat_keluar_' . Str::slug($templateData->name) . '_' . Str::slug($dataSurat['nama'] ?? 'unknown') . '_' . time() . '.pdf';
                        $pdfFilePathInStorage = 'surat_keluar/' . $pdfFileName; // Path relatif di storage disk 'public'

                        try {
                            // Menyiapkan data untuk view Blade template surat.
                            $viewData = [
                                'pengajuan' => $record,
                                'user' => $userData,
                                'template' => $templateData,
                                'linkFiles' => $dataSurat['link_files'] ?? []
                            ];

                            // Menambahkan semua data_surat ke $viewData kecuali 'link_files'.
                            foreach ($dataSurat as $key => $value) {
                                if (!in_array($key, ['link_files'])) {
                                    $viewData[$key] = $value;
                                }
                            }
                            $viewData['prodi'] = $dataSurat['prodi'] ?? $prodiUser;

                            // Merender view Blade menjadi HTML dan mengkonversinya ke PDF, lalu menyimpannya.
                            $pdfContent = view('templates.' . $templateData->class_name, $viewData)->render();
                            Storage::disk('public')->put($pdfFilePathInStorage, Pdf::loadHTML($pdfContent)->output());

                        } catch (\Exception $e) {
                            // Menampilkan notifikasi jika ada error saat merender atau menyimpan PDF.
                            Notification::make()
                                ->title('Gagal Membuat PDF')
                                ->body('Terjadi kesalahan saat merender PDF: ' . $e->getMessage())
                                ->danger()
                                ->send();
                            return;
                        }

                        // Mencari atau membuat record SuratKeluar yang terkait dengan Pengajuan ini.
                        $suratKeluar = \App\Models\SuratKeluar::firstOrNew([
                            'pengajuan_id' => $record->_id, // Cari berdasarkan pengajuan_id
                        ]);

                        // Jika record SuratKeluar sudah ada dan memiliki PDF lama, hapus file lama tersebut.
                        if ($suratKeluar->exists && $suratKeluar->pdf_url) {
                            // Hapus draf PDF lama
                            Storage::disk('public')->delete('surat_keluar/' . $suratKeluar->pdf_url);
                            // Hapus juga PDF final lama jika ada (penting untuk membersihkan storage)
                            Storage::disk('public')->delete('surat_keluar_final/' . $suratKeluar->pdf_url);
                            \Log::info('File PDF lama dihapus:', ['file' => $suratKeluar->pdf_url]);
                        }

                        // Mengisi properti model SuratKeluar dengan data terbaru.
                        $suratKeluar->fill([
                            'nomor_surat' => $nomorSurat,
                            'prodi' => $prodiUser,
                            'pdf_url' => $pdfFileName, // Simpan nama file PDF draf yang baru
                            'template_id' => $templateData->_id,
                            'metadata' => $dataSurat
                        ]);

                        $suratKeluar->save(); // Menyimpan atau memperbarui record SuratKeluar.

                        // Memperbarui Pengajuan dengan ID Surat Keluar dan mengubah statusnya.
                        $updateData = [
                            'surat_keluar_id' => $suratKeluar->_id, // Menunjuk ke _id SuratKeluar yang di-update/dibuat
                        ];

                        // Ubah status pengajuan menjadi 'menunggu_ttd' setelah draf dibuat.
                        // Pastikan tidak mengubah status jika sudah 'selesai' atau 'ditolak'.
                        if ($record->status !== 'selesai' && $record->status !== 'ditolak') {
                            $updateData['status'] = 'menunggu_ttd';
                        }
                        
                        $record->update($updateData); // Memperbarui record Pengajuan.

                        // Mengirim notifikasi sukses kepada admin.
                        Notification::make()
                            ->title('Draf Surat Keluar Berhasil Dibuat') // Ubah judul notifikasi
                            ->body('Nomor Surat: ' . $nomorSurat . ' telah dibuat. Status pengajuan diubah menjadi "Menunggu Tanda Tangan". <a href="' . asset('storage/' . $pdfFilePathInStorage) . '" target="_blank" class="underline">Lihat Draf PDF</a>') // Tambah info status dan link draf
                            ->success()
                            ->send();

                        // Mengarahkan kembali ke halaman pengajuan yang sedang diedit.
                        return redirect()->route('filament.admin.resources.pengajuans.edit', ['record' => $record->getKey()]);

                    } catch (\Exception $e) {
                        // Menampilkan notifikasi error jika terjadi kesalahan umum.
                        Notification::make()
                            ->title('Gagal Membuat Surat Keluar')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                // Kondisi visibilitas tombol 'Generate Surat Keluar'.
                // Tombol ini hanya akan muncul jika:
                // 1. Pengguna adalah admin.
                // 2. Status pengajuan adalah 'diproses' atau 'menunggu_ttd'.
                // 3. Belum ada record SuratKeluar, ATAU record SuratKeluar ada tapi belum ada 'pdf_url' yang terisi (artinya belum ada PDF final).
                ->visible(function (Pengajuan $record): bool {
                    $hasSuratKeluar = $record->suratKeluar()->exists();
                    $hasFinalPdf = $hasSuratKeluar && !empty($record->suratKeluar->pdf_url);

                    return auth()->user()->is_admin &&
                           in_array($record->status, ['diproses', 'menunggu_ttd']) &&
                           !$hasFinalPdf; // Tombol tidak muncul jika sudah ada PDF final
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
