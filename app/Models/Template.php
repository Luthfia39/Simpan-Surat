<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Template extends Model
{
    use HasFactory;

    protected $collection = 'templates';

    protected $fillable = [
        'for_user',
        'name',
        'class_name',
    ];

    public function scopeForUser(Builder $query)
    {
        return $query->where('for_user', true);
    }
}
