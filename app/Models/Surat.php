<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; // Gunakan MongoDB Model

class Surat extends Model
{
    protected $connection = 'mongodb'; // Pakai koneksi MongoDB
    protected $collection = 'surat';   // Nama koleksi di MongoDB
    protected $fillable = ['nomor_surat', 'tanggal_surat', 'isi_surat'];
}
