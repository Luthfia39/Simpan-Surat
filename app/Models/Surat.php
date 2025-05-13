<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Surat extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'surats';

    protected $fillable = [
        'task_id',
        'ocr_text',
        'letter_type',
        'extracted_fields',
        'pdf_url',
    ];

    protected $casts = [
        'extracted_fields' => 'array',
    ];
}
