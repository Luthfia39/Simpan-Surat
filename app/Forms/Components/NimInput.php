<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;

class NimInput extends TextInput
{
    public function format(): static
    {
        return $this
            ->placeholder('00/000000/SV/00000')
            ->mask('99/999999/aa/99999')
            ->regex('/^\d{2}\/\d{6}\/[A-Z]{2}\/\d{5}$/');
    }
}