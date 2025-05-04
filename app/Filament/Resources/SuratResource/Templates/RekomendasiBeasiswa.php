<?php

namespace App\Filament\Resources\SuratResource\Templates;

use App\Forms\Components\IpkInput;
use App\Forms\Components\NimInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;

class RekomendasiBeasiswa extends CreateTemplate
{
    public static ?string $view = 'templates.rekomendasi-beasiswa';

    public static function getSchema(): array
    {
        return [
            Section::make([
                TextInput::make('nama')
                    ->minLength(2)
                    ->required(),
                NimInput::make('nim')
                    ->label('NIM')
                    ->validationAttribute('NIM')
                    ->format()
                    ->required(),
                IpkInput::make('ipk')
                    ->label('IPK')
                    ->validationAttribute('IPK')
                    ->format()
                    ->required(),
                TextInput::make('sks')
                    ->label('SKS')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(144)
                    ->required(),
                TextInput::make('alamat')
                    ->minLength(2)
                    ->maxLength(255)
                    ->required(),
                TextInput::make('beasiswa')
                    ->label('Pemberi Beasiswa')
                    ->minLength(2)
                    ->maxLength(255)
                    ->required(),
            ])
                ->columns(2),
        ];
    }
}
