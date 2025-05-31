<?php

namespace App\Filament\Resources\TemplateResource\Templates;

use App\Enums\Major;
use App\Forms\Components\NimInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class KeteranganAlumni extends CreateTemplate
{
    public static ?string $view = 'templates.keterangan-alumni';

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
                TextInput::make('thn_lulus')
                    ->label('Tahun Lulus')
                    ->mask('9999')
                    ->regex('/^\d{4}$/')
                    ->required(),
                TextInput::make('keterangan')
                    ->label('Tujuan Pengajuan')
                    ->helperText('Contoh: untuk keperluan melengkapi berkas')
                    ->minLength(5)
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->required(),
            ])
                ->columns(2),

        ];
    }
}
