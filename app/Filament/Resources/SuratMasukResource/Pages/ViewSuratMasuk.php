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
                    ->getStateUsing(fn ($record): ?string => 
                        // $record->nomor_surat ?? '-'
                        (is_string($record->extracted_fields) && ($decodedFields = json_decode($record->extracted_fields, true)) && is_array($decodedFields) && isset($decodedFields['nomor_surat']['text']))
                        ? $decodedFields['nomor_surat']['text']
                        : '-'
                        )
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
                    ->getStateUsing(fn ($record): ?string => 
                        (is_string($record->extracted_fields) && ($decodedFields = json_decode($record->extracted_fields, true)) && is_array($decodedFields) && isset($decodedFields['ttd_surat']['text']))
                        ? $decodedFields['ttd_surat']['text']
                        : '-'
                    )
                    ->icon('heroicon-o-user'),
                    
                TextEntry::make('Tanggal pembuatan')
                    ->getStateUsing(fn ($record): ?string => 
                        (is_string($record->extracted_fields) && ($decodedFields = json_decode($record->extracted_fields, true)) && is_array($decodedFields) && isset($decodedFields['tanggal']['text']))
                        ? $decodedFields['tanggal']['text']
                        : '-'
                    )
                    ->badge()
                    ->color('warning'),

                TextEntry::make('Isi Surat')
                    ->getStateUsing(fn ($record): ?string => 
                        (is_string($record->extracted_fields) && ($decodedFields = json_decode($record->extracted_fields, true)) && is_array($decodedFields) && isset($decodedFields['isi_surat']['text']))
                        ? $decodedFields['isi_surat']['text']
                        : '-'
                    )
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
