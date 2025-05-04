<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Template;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Template::insert([
            [
                'name' => 'Surat Keterangan Aktif Kuliah',
                'class_name' => 'KeteranganAktifKuliah',
            ],
            [
                'name' => 'Surat Keterangan Alumni',
                'class_name' => 'KeteranganAlumni',
            ],
            [
                'name' => 'Surat Tugas Magang',
                'class_name' => 'MagangMbkm',
            ],
            [
                'name' => 'Surat Pengantar Penelitian',
                'class_name' => 'PengantarPenelitian',
            ],
            [
                'name' => 'Surat Pengantar Praktik Industri',
                'class_name' => 'PengantarPraktikIndustri',
            ],
            [
                'name' => 'Surat Tugas Praktik Industri',
                'class_name' => 'PraktikIndustri',
            ],
            [
                'name' => 'Surat Rekomendasi Beasiswa',
                'class_name' => 'RekomendasiBeasiswa',
            ],
        ]);
    }
}
