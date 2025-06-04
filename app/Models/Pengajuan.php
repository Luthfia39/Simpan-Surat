<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengajuan extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'pengajuans';

    protected $fillable = [
        'user_id', 
        'link_files',
        'status',
        'template_id',
        'data_surat',
        'keterangan',
        'surat_keluar_id'
    ];

    protected $casts = [
        'link_files' => 'array',
        'data_surat' => 'array',
    ]

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Template::class, 'template_id');
    }

    public function suratKeluar(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SuratKeluar::class, 'surat_keluar_id');
    }
}