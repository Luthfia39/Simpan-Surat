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
        ]);
    }
}
