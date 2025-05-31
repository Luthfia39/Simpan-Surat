<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Prodi;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus semua data yang ada di koleksi prodi (opsional)
        Prodi::truncate();

        // Masukkan data dummy ke koleksi prodi
        Prodi::insert([
            ['kode' => 'TRPL', 'nama' => 'Teknologi Rekayasa Perangkat Lunak'],
            ['kode' => 'TRI', 'nama' => 'Teknologi Rekayasa Internet'],
            ['kode' => 'TRE', 'nama' => 'Teknologi Rekayasa Elektro'],
            ['kode' => 'TRIK', 'nama' => 'Teknologi Rekayasa Instrumentasi dan Kontrol'],
        ]);
    }
}
