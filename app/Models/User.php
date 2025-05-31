<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

// use Jenssegers\Mongodb\Auth\User as Authenticatable;
// class User extends Authenticatable implements FilamentUser

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

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
        'prodi',
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
        if ($panel->getId() === 'user' && str_ends_with($this->email, '@mail.ugm.ac.id')) {
            return true;
        }
        return false;
        // return str_ends_with($this->email, '@mail.ugm.ac.id');
    }
}
