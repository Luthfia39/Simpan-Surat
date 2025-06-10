<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PengajuanStatistic extends BaseWidget
{
    protected function getStats(): array
    {
        $pending = \App\Models\Pengajuan::where('status', 'pending')->count();
        $diproses = \App\Models\Pengajuan::where('status', 'diproses')->count();
        $ditolak = \App\Models\Pengajuan::where('status', 'ditolak')->count();
        $selesai = \App\Models\Pengajuan::where('status', 'selesai')->count();
        $totalPengajuan = $pending + $diproses + $ditolak + $selesai;

        return [
            Stat::make('Total Pengajuan', $totalPengajuan)
                ->color('#2C3E50')
                ->extraAttributes([
                    'style' => '--tw-ring-color: #2C3E50',
                ]), 

            Stat::make('Pengajuan Pending', $pending)
                ->color('#EF4444')
                ->extraAttributes([
                    'style' => '--tw-ring-color: #EF4444',
                ]), 

            Stat::make('Pengajuan Diproses', $diproses)
                ->color('primary')
                ->extraAttributes([
                    'style' => '--tw-ring-color: blue',
                ]), 

            Stat::make('Pengajuan Ditolak', $ditolak)
                ->color('#F59E0B')
                ->extraAttributes([
                    'style' => '--tw-ring-color: #F59E0B',
                ]),

            Stat::make('Pengajuan Selesai', $selesai)
                ->color('#10B981')
                ->extraAttributes([
                    'style' => '--tw-ring-color: #10B981',
                ]),
        ];
    }
}
