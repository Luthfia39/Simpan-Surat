<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateResource\Pages;
use App\Filament\Resources\TemplateResource\RelationManagers;
use App\Models\Template;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
// using mongoDB
use MongoDB\Laravel\Eloquent\Model;

use App\Models\User;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $pluralLabel = 'Templates Surat';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar Jenis Surat')
                    ->description('Informasi umum tentang jenis surat.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Surat')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, table: Template::class, column: 'name'), // Perbaiki table/column jika unique hanya untuk field 'name' di Template
                        Forms\Components\TextInput::make('class_name')
                            ->label('Nama Blade View (misal: magang)')
                            ->helperText('Akan merujuk ke resources/views/templates/{classname}.blade.php untuk rendering PDF/UI.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, table: Template::class, column: 'class_name'), // Perbaiki table/column jika unique hanya untuk field 'class_name' di Template
                        Forms\Components\Toggle::make('for_user')
                            ->label('Tersedia untuk Pengguna Umum')
                            ->helperText('Jika aktif, surat ini akan muncul di daftar pilihan pengguna.')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Definisi Input Data Spesifik Surat')
                    ->description('Tentukan field-field input yang dibutuhkan pengguna untuk mengisi data surat ini.')
                    ->schema([
                        Forms\Components\Repeater::make('form_schema')
                            ->label('Field Input')
                            ->helperText('Definisikan nama field (kunci di data_surat), label tampilan, dan tipe input.')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Field (kunci)')
                                    ->helperText('Gunakan snake_case (misal: nama_lengkap, tgl_mulai). Harus unik dalam satu template.')
                                    ->required()
                                    // Unique Rule untuk Repeater: Pastikan unik DALAM INSTANCE REPEATER INI.
                                    // Rule unique global di dalam repeater nested sangat kompleks dan sering dihindari.
                                    // Jika ingin unique across all templates, perlu validasi kustom.
                                    ->unique(ignoreRecord: true, table: Template::class, column: 'form_schema.*.name'), // Ini hanya memastikan unik untuk field 'name' dalam 'form_schema' dari Template yang sama.
                                Forms\Components\TextInput::make('label')
                                    ->label('Label Tampilan')
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->label('Tipe Input')
                                    ->options([
                                        'text' => 'Teks Pendek',
                                        'textarea' => 'Teks Panjang',
                                        'number' => 'Angka',
                                        'date' => 'Tanggal',
                                        'email' => 'Email',
                                        'url' => 'URL',
                                        'select' => 'Dropdown (Pilihan)',
                                        'repeater' => 'Daftar Berulang (Repeater)', // <--- TAMBAHKAN TIPE INI
                                    ])
                                    ->required()
                                    ->live(),

                                Forms\Components\TextInput::make('helper_text')
                                    ->label('Teks Bantuan (Opsional)')
                                    ->helperText('Akan muncul di bawah input field.')
                                    ->nullable()
                                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'textarea', 'number', 'date', 'email', 'url', 'select', 'repeater'])), // Tampilkan juga untuk repeater

                                Forms\Components\TextInput::make('default')
                                    ->label('Nilai Default (Opsional)')
                                    ->nullable()
                                    ->visible(fn (Forms\Get $get) => $get('type') !== 'repeater'), // Sembunyikan default untuk tipe repeater

                                Forms\Components\Toggle::make('required')
                                    ->label('Wajib Diisi')
                                    ->default(false),

                                // Field untuk Opsi Dropdown (Hanya Tampil Jika Tipe = 'select')
                                Forms\Components\Repeater::make('options')
                                    ->label('Opsi Dropdown')
                                    ->helperText('Definisikan nilai (value) dan label tampilan untuk setiap opsi dropdown.')
                                    ->schema([
                                        Forms\Components\TextInput::make('value')
                                            ->label('Nilai Opsi')
                                            ->required(),
                                        Forms\Components\TextInput::make('label')
                                            ->label('Label Tampilan Opsi')
                                            ->required(),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                    ->default([])
                                    ->columnSpanFull()
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'select'),

                                // === KODE REPEATER 'kelompok' BARU DITAMBAHKAN DI SINI ===
                                Forms\Components\Repeater::make('sub_schema') // Nama field untuk skema repeater internal
                                    ->label('Skema Field untuk Repeater Ini')
                                    ->helperText('Definisikan field-field yang ada di dalam setiap item repeater (misal: nama, NIM).')
                                    ->default([])
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null) // Item label dari sub-field 'label'
                                    ->schema([ // Skema untuk field di dalam repeater ini
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama Sub-Field (kunci)')
                                            ->helperText('Gunakan snake_case (misal: nama, nim).')
                                            ->required()
                                            ->unique(ignoreRecord: true, table: Template::class, column: 'form_schema.*.sub_schema.*.name'), // Unique untuk sub-field
                                        Forms\Components\TextInput::make('label')
                                            ->label('Label Tampilan Sub-Field')
                                            ->required(),
                                        Forms\Components\Select::make('type')
                                            ->label('Tipe Input Sub-Field')
                                            ->options([ // Opsi tipe input untuk sub-field
                                                'text' => 'Teks Pendek',
                                                'textarea' => 'Teks Panjang',
                                                'number' => 'Angka',
                                                'date' => 'Tanggal',
                                                'email' => 'Email',
                                                'url' => 'URL',
                                                'select' => 'Dropdown (Pilihan)', // Hati-hati nested select di repeater
                                                // 'repeater' => 'Repeater (Jangan nested lagi!)', // Hindari terlalu banyak nesting repeater
                                            ])
                                            ->required()
                                            ->live(), // Penting agar helper_text, default, dll. bisa kondisional

                                        Forms\Components\TextInput::make('helper_text')
                                            ->label('Teks Bantuan Sub-Field (Opsional)')
                                            ->nullable()
                                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'textarea', 'number', 'date', 'email', 'url'])),

                                        Forms\Components\TextInput::make('default')
                                            ->label('Nilai Default Sub-Field (Opsional)')
                                            ->nullable()
                                            ->visible(fn (Forms\Get $get) => $get('type') !== 'repeater'),

                                        Forms\Components\Toggle::make('required')
                                            ->label('Wajib Diisi Sub-Field')
                                            ->default(false),
                                    ])
                                    ->columnSpanFull()
                                    ->grid(2) // Tata letak grid untuk sub-field
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'repeater'), // <--- HANYA TAMPIL JIKA TIPE = 'repeater'
                                // === AKHIR KODE REPEATER 'kelompok' BARU DITAMBAHKAN ===

                            ])
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->grid(2)
                            ->default([]),
                    ]),

                Forms\Components\Section::make('Definisi Berkas yang Dibutuhkan')
                    ->description('Tentukan file-file yang perlu diunggah pengguna untuk surat ini.')
                    ->schema([
                        Forms\Components\Repeater::make('required_files')
                            ->label('Berkas yang Dibutuhkan')
                            ->helperText('Definisikan nama field (kunci di data_surat.link_files) dan label tampilan.')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Field Berkas (kunci)')
                                    ->helperText('Gunakan snake_case (misal: file_cv, file_transkrip). Harus unik.')
                                    ->required()
                                    ->unique(ignoreRecord: true, table: Template::class, column: 'required_files.*.name'),
                                Forms\Components\TextInput::make('label')
                                    ->label('Label Berkas')
                                    ->required(),
                            ])
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->grid(2)
                            ->default([]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    IconColumn::make('name')
                        ->icon('heroicon-s-document'),
                    TextColumn::make('name')
                        ->label('Nama')
                        ->size(TextColumn\TextColumnSize::Medium)
                        ->searchable(true),
                ])->space(2),
            ])
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'xl' => 3,
            ])
            ->defaultSort('name')
            // ->recordUrl(
            //     fn (Model $record): string => Pages\Edit::getUrl([$record->id]),
            // )
            ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateSuratFromTemplate::route('/create'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
            // 'view' => Pages\ViewTemplate::route('/{record}')
        ];
    }
}
