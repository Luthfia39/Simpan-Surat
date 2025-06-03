<?php

namespace App\Policies;

use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SuratKeluarPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SuratKeluar $suratKeluar): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SuratKeluar $suratKeluar): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SuratKeluar $suratKeluar): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SuratKeluar $suratKeluar): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SuratKeluar $suratKeluar): bool
    {
        return false;
    }
}
