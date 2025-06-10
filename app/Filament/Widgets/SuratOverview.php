<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Surat;
use App\Models\SuratKeluar;
use App\Models\Pengajuan;

class SuratOverview extends ChartWidget
{
    protected static ?int $sort = 2;
    protected array|string|int $columnSpan = 'full';
    public ?string $filter = '';

    public function __construct()
    {
        $this->filter = now()->year;
    }

    public function getHeading(): string
    {
        $tahun = $this->filter;
        return "Statistik Surat Tahun $tahun";
    }

    protected function getFilters(): ?array
    {
        $tahunSekarang = now()->year;
        $rangeTahun = range($tahunSekarang - 5, $tahunSekarang + 1); // Tahun dari 2020 - 2026
        $options = array_combine($rangeTahun, $rangeTahun);

        // dd($options);

        return $options;
    }

    protected function getData(): array
    {
        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $tahun = $this->filter;

        $suratMasukData = [];
        $suratKeluarData = [];
        $pengajuanData = [];

        foreach ($bulan as $index => $namaBulan) {
            $month = $index + 1;

            $masuk = Surat::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $month)
                ->count();

            $keluar = SuratKeluar::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $month)
                ->count();
            $pengajuan = Pengajuan::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $month)
                ->count();

            $suratMasukData[] = $masuk;
            $suratKeluarData[] = $keluar;
            $pengajuanData[] = $pengajuan;
        }

        return [
            'labels' => $bulan,
            'datasets' => [
                [
                    'label' => 'Surat Masuk',
                    'data' => $suratMasukData,
                    'borderColor' => '#3b82f6', 
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Surat Keluar',
                    'data' => $suratKeluarData,
                    'borderColor' => '#10b981', 
                    'backgroundColor' => '#10b981',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
