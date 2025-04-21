<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Signer;

class SignerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Signer::create(['name' => 'Ir. Nur Rohman Rosyid, S.T., M.T., D.Eng', 'worker_number' => '111197510201206101', 'department' => 'Ketua Departemen TEDI']);
        Signer::create(['name' => '	Dr. Umar Taufiq, S.Kom., M.Cs.', 'worker_number' => '111198212201610101', 'department' => 'Ketua Program Studi TRPL']);
        // Add more signers as needed
    }
}
