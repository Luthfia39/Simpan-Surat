<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Pengajuan;
use App\Models\SuratKeluar;

class Template extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'templates';

    protected $fillable = [
        'for_user',
        'name',
        'class_name',
        'form_schema',
        'required_files',
    ];

    protected $casts = [
        'for_user' => 'boolean',
        'form_schema' => 'array',
        'required_files' => 'array',
    ];

    /**
     * Get all of the pengajuan for the Template
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pengajuans(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'template_id');
    }

    /**
     * Get all of the suratKeluars for the Template
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function suratKeluars(): HasMany
    {
        return $this->hasMany(SuratKeluar::class, 'template_id');
    }

    public function scopeForUser(Builder $query)
    {
        return $query->where('for_user', true);
    }
}
