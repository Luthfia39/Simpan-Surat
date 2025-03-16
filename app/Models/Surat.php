<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Surat extends Model
{
    use HasFactory;

    protected $connection = 'mongodb'; // ✅ Koneksi MongoDB
    protected $collection = 'surats'; // ✅ Nama collection di MongoDB

    protected $fillable = [
        'type',
        'nomor_surat',
        'tanggal',
        'pengirim',
        'penerima',
        'alamat',
        'isi_surat'
    ];
}
