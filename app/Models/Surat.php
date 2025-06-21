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
        'is_ugm',
        'letter_type',
        'document_index',
        'extracted_fields',
        'pdf_url',
        'review_status'
    ];

    protected $casts = [
        'extracted_fields' => 'array',
    ];
}
