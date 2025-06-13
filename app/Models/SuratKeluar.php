<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Prodi;
use App\Models\Template;
use App\Models\Pengajuan;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'surat_keluars';

    protected $fillable = [
        'nomor_surat',
        'prodi_id',
        'jenis_surat',
        'pdf_url',
        'template_id',
        'pengajuan_id',
        'metadata'
    ];

    protected function casts(): array
    {
        return 
        [
            'metadata' => 'array', 
            'pdf_url' => 'string', 
        ];
    }

    /**
     * Get the prodi that owns the SuratKeluar
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    /**
     * Get the template that owns the SuratKeluar
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    /**
     * Get the pengajuan that owns the SuratKeluar
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }
}
