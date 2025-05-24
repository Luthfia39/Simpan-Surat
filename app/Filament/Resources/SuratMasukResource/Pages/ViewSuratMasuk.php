<?php

namespace App\Filament\Resources\SuratMasukResource\Pages;

use App\Filament\Resources\SuratMasukResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;

class ViewSuratMasuk extends ViewRecord
{
    protected static string $resource = SuratMasukResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('Nomor Surat')
                    ->getStateUsing(fn ($record) => $record->extracted_fields['nomor_surat'][0] ?? '-')
                    ->icon('heroicon-o-hashtag'),

                TextEntry::make('Jenis Surat')
                    ->getStateUsing(fn ($record) => ucwords(str_replace('_', ' ', $record->letter_type)))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Surat Pernyataan' => 'warning',
                        'Surat Keterangan' => 'info',
                        'Surat Tugas' => 'success',
                        default => 'gray'
                    }),

                TextEntry::make('Penanda Tangan')
                    ->getStateUsing(fn ($record) => $record->extracted_fields['penanda_tangan'][0] ?? '-')
                    ->icon('heroicon-o-user'),
                    
                TextEntry::make('Tanggal pembuatan')
                    ->getStateUsing(fn ($record) => $record->extracted_fields['tanggal'][0] ?? '-')
                    ->badge()
                    ->color('warning'),

                TextEntry::make('Teks Surat')
                    ->getStateUsing(fn ($record) => $record->ocr_text ?? '-')
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'text-justify']),

                // ⬇️ Bagian untuk download PDF
                Actions::make([
                    Action::make('downloadPdf')
                        ->label('Download Berkas')
                        ->url(fn ($record): string => asset('storage/suratMasuk/' . $record->pdf_url))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-arrow-down-tray'),
                ])->columnSpanFull(),
            ]);
    }
}
