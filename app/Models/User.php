<?php

namespace App\Models;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

// use Jenssegers\Mongodb\Auth\User as Authenticatable;
// class User extends Authenticatable implements FilamentUser

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Models\Prodi;
use App\Models\Pengajuan;

class User extends Eloquent implements AuthenticatableContract, FilamentUser
{
    use Authenticatable; // Trait untuk autentikasi Laravel

    protected $connection = 'mongodb';
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'google_avatar',
        'email_verified_at',
        'is_admin',
        'nim',
        'prodi_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (str_ends_with($this->email, '@ugm.ac.id') || str_ends_with($this->email, '@mail.ugm.ac.id')) {
            if ($panel->getId() === 'admin') {
                return $this->is_admin === true; // Hanya user dengan is_admin = true yang bisa akses panel admin
            }
        
            if ($panel->getId() === 'user') {
                return $this->is_admin === false; // Semua user yang terautentikasi bisa akses panel user
            }
        }
        return false;
    }

    /**
     * Get the prodi that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    /**
     * Get all of the pengajuans for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pengajuans(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'pengajuan_id');
    }
}
