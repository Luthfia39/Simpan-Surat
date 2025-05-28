<?php

namespace App\Filament\Resources\TemplateResource\Templates;

use App\Forms\Components\IpkInput;
use App\Forms\Components\NimInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use App\Enums\Major;
use Filament\Forms\Components\Select;

class RekomendasiBeasiswa extends CreateTemplate
{
    public static ?string $view = 'templates.rekomendasi-beasiswa';

    public static function getSchema(): array
    {
        return [
            Section::make([
                TextInput::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->minLength(1)
                    ->mask('999999999999999999')
                    ->required(),
                TextInput::make('nama')
                    ->minLength(2)
                    ->required(),
                NimInput::make('nim')
                    ->label('NIM')
                    ->validationAttribute('NIM')
                    ->format()
                    ->required(),
                Select::make('prodi')
                    ->label('Program Studi')
                    ->options(Major::toArray())
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
