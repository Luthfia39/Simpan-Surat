<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;

class IpkInput extends TextInput
{
    public function format(): static
    {
        return $this
            ->placeholder('0.00 - 4.00')
            ->numeric()
            ->inputMode('decimal')
            ->step(.1)
            ->regex('/^[0-4]\.\d{2}$/');
    }
}