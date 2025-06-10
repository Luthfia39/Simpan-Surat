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
                ->color('primary')
                ->extraAttributes([
                    'style' => '--tw-ring-color: blue',
                ]), 

            Stat::make('Surat Keluar', $suratKeluar)
                ->color('#F59E0B')
                ->extraAttributes([
                    'style' => '--tw-ring-color: #F59E0B',
                ]),

            Stat::make('Surat Masuk', $suratMasuk)
                ->color('#10B981')
                ->extraAttributes([
                    'style' => '--tw-ring-color: #10B981',
                ]), 
        ];
    }
}
