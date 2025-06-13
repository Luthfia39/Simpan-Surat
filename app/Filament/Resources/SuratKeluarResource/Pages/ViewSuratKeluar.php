<?php

namespace App\Filament\Resources\SuratKeluarResource\Pages;

use App\Filament\Resources\SuratKeluarResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Infolist;
use App\Enums\Major;

class ViewSuratKeluar extends ViewRecord
{
    protected static string $resource = SuratKeluarResource::class;

    protected ?string $heading = "Detail surat keluar";

    // protected ?string $subheading = "Detail surat keluar";

    // protected static string $view = 'filament.resources.surat-keluar-resource.pages.view-surat-keluar';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('Nomor Surat')
                    ->getStateUsing(fn ($record) => $record->nomor_surat ?? '-')
                    ->icon('heroicon-o-hashtag'),

                TextEntry::make('Pengaju Surat')
                    ->getStateUsing(fn ($record) => $record->metadata['nama'] ?? '-')
                    ->icon('heroicon-o-user-circle'),

                TextEntry::make('Jenis Surat')
                    ->getStateUsing(fn ($record) => $record->template->name)
                    ->badge()
                    ->color('success'),

                TextEntry::make('Prodi')
                    ->getStateUsing(function ($record) {
                        // Mengakses kode prodi dari metadata
                        $prodiCode = $record->metadata['prodi'] ?? null;

                        // Mengambil nama lengkap dari enum jika kode prodi ada
                        if ($prodiCode) {
                            return Major::getNameByCode($prodiCode) ?? $prodiCode; // Fallback ke kode jika nama tidak ditemukan
                        }

                        return '-'; // Jika kode prodi tidak ada
                    }),

                // ⬇️ Bagian untuk download PDF
                Actions::make([
                    Action::make('downloadPdf')
                        ->label('Download Berkas')
                        ->url(fn ($record): string => asset('storage/surat_keluar/' . $record->pdf_url))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-arrow-down-tray'),
                ])->columnSpanFull(),
            ]);
    }
}
