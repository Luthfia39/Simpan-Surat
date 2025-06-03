<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Pengajuan extends Model
{
    protected $collection = 'pengajuans';
    protected $fillable = [
        'user_id', 
        'link_files',
        'status',
        'jenis_surat',
        'data_surat',
        'keterangan',
        'link_surat'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}