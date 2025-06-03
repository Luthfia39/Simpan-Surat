<?php

namespace App\Filament\Resources\TemplateResource\Templates;

use App\Enums\Major;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Forms\Components\NimInput;

use Illuminate\Support\Facades\Auth;

class KeteranganAktifKuliah extends CreateTemplate
{
    public static ?string $view = 'templates.keterangan-aktif-kuliah';

    public static function getSchema(): array
    {
        return [
            Section::make([
                TextInput::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->minLength(1)
                    ->mask('999999999999999999')
                    ->required()
                    ->hidden(!Auth::user()->is_admin),
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
                Select::make('semester')
                    ->options([
                        'Gasal' => 'Gasal',
                        'Genap' => 'Genap',
                    ])
                    ->required(),
                TextInput::make('thn_akademik')
                    ->label('Tahun Akademik')
                    ->mask('9999/9999')
                    ->required(),
                TextInput::make('keterangan')
                    ->label('Tujuan Pengajuan')
                    ->helperText('Contoh: untuk mendapatkan tunjangan')
                    ->minLength(5)
                    ->columnSpanFull()
                    ->required(),
            ])
                ->columns(2),
            Section::make([
                TextInput::make('nama_ortu')
                    ->label('Nama Orang Tua/Wali')
                    ->minLength(2)
                    ->required(),
                TextInput::make('pekerjaan')
                    ->label('Pekerjaan Orang Tua/Wali')
                    ->minLength(2)
                    ->required(),
                TextInput::make('nip')
                    ->label('NIP')
                    ->minLength(18)
                    ->mask('999999999999999999')
                    ->required(),
                TextInput::make('pangkat')
                    ->label('Pangkat/Golongan')
                    ->minLength(2)
                    ->required(),
                TextInput::make('instansi')
                    ->minLength(2)
                    ->required(),
            ])
                ->columns(2),
        ];
    }
}