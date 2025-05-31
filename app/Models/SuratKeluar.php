<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'surat_keluar';

    protected $fillable = [
        'major',
        'letter_type',
        'pdf_url',
    ];
}
