<?php

namespace App\Filament\Resources\SuratResource\Templates;

abstract class CreateTemplate
{
    /**
     * @var view-string
     */
    public static ?string $view = null;

    /**
     * Define form schema.
     *
     * @return array
     */
    abstract public static function getSchema();
}