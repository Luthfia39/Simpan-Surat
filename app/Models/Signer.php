<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Signer extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'signers';

    protected $fillable = [
        'name',
        'worker_number',
        'department',
    ];
}
