<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuratStatistic extends BaseWidget
{
    protected function getStats(): array
    {
        $suratMasuk = \App\Models\Surat::count();
        $suratKeluar = \App\Models\SuratKeluar::count();
        $totalSurat = $suratMasuk + $suratKeluar;

        return [
            Stat::make('Total Surat', $suratMasuk + $suratKeluar)
                ->description('Surat masuk & keluar')
                // ->descriptionIcon('heroicon-o-arrow-path')
                ->color('warning'), // Kuning

            Stat::make('Surat Keluar', $suratKeluar)
                ->description('Jumlah surat keluar terbaru')
                // ->descriptionIcon('heroicon-o-arrow-path')
                ->color('primary'), // Biru

            Stat::make('Surat Masuk', $suratMasuk)
                ->description('Jumlah surat masuk terbaru')
                ->descriptionColor('danger') // Merah
                ->color('secondary'), // Hijau
        ];
    }
}
