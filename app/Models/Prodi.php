<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\SuratKeluar;

class Prodi extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'prodis'; 
    protected $fillable = ['kode', 'nama'];
}

 /**
 * Get all of the users for the Prodi
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function users(): HasMany
{
    return $this->hasMany(User::class, 'prodi_id');
}

/**
 * Get all of the suratKeluars for the Prodi
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function suratKeluars(): HasMany
{
    return $this->hasMany(SuratKeluar::class, 'prodi_id');
}