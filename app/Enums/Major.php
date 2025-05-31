<?php

namespace App\Enums;

use App\Models\Prodi;

class Major
{
    /**
     * Ambil semua data prodi dari MongoDB dalam format array.
     *
     * @return array
     */
    public static function toArray(): array
    {
        return Prodi::all()->pluck('nama', 'kode')->toArray();
    }

    /**
     * Dapatkan nama berdasarkan kode dari MongoDB.
     *
     * @param string $kode
     * @return string|null
     */
    public static function getNameByCode(string $kode): ?string
    {
        return Prodi::where('kode', $kode)->value('nama');
    }
}