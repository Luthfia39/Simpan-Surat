<?php

namespace App\Filament\Resources\TemplateResource\Templates;

use App\Enums\Major;
use App\Forms\Components\NimInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class PraktikIndustri extends CreateTemplate
{
    public static ?string $view = 'templates.praktik-industri';

    public static function getSchema(): array
    {
        return [
            Section::make([
                TextInput::make('perusahaan')
                    ->minLength(2)
                    ->required(),
                TextInput::make('dospem')
                    ->label('Dosen Pembimbing')
                    ->minLength(2)
                    ->required(),
                DatePicker::make('tgl_mulai')
                    ->label('Tanggal Mulai')
                    ->before('tgl_selesai')
                    ->required(),
                DatePicker::make('tgl_selesai')
                    ->label('Tanggal Selesai')
                    ->required(),
                Select::make('prodi')
                    ->label('Program Studi')
                    ->options(Major::toArray())
                    ->required(),
            ])
                ->columns(2),
            Section::make([
                Repeater::make('kelompok')
                    ->schema([
                        TextInput::make('nama')
                            ->minLength(2)
                            ->required(),
                        NimInput::make('nim')
                            ->label('NIM')
                            ->validationAttribute('NIM')
                            ->format()
                            ->required(),
                    ])
                    ->addActionLabel('Tambah Anggota')
                    ->reorderableWithButtons()
                    ->columns(2)
                    ->columnSpan('full')
                    ->maxItems(10)
                    ->required(),
            ]),
        ];
    }
}
