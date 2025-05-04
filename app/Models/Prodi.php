<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Prodi extends Model
{
    protected $collection = 'prodi'; 
    protected $fillable = ['kode', 'nama'];
}
