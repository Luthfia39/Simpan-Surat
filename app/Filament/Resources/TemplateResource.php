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
// using mongoDB
use MongoDB\Laravel\Eloquent\Model;

use App\Models\User;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Manajemen Surat';

    protected static ?string $pluralLabel = 'Templates Surat';

    // protected static ?int $navigationSort = 3;

    // protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar Template')
                    ->description('Informasi umum tentang template surat.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Template')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true), // Pastikan nama template unik
                        Forms\Components\TextInput::make('class_name')
                            ->label('Nama Blade View (misal: magang)')
                            ->helperText('Akan merujuk ke resources/views/templates/{classname}.blade.php untuk rendering PDF/UI.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true), // Pastikan classname unik
                        Forms\Components\Toggle::make('for_user')
                            ->label('Tersedia untuk Pengguna Umum')
                            ->helperText('Jika aktif, template ini akan muncul di daftar pilihan pengguna.')
                            ->default(true),
                    ])->columns(2), // Tampilkan dalam 2 kolom

                Forms\Components\Section::make('Definisi Input Data Spesifik Surat')
                    ->description('Tentukan field-field input yang dibutuhkan pengguna untuk mengisi data surat ini.')
                    ->schema([
                        Forms\Components\Repeater::make('form_schema')
                            ->label('Field Input')
                            ->helperText('Definisikan nama field (kunci di data_surat), label tampilan, dan tipe input.')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Field (kunci)')
                                    ->helperText('Gunakan snake_case (misal: nama_lengkap, tgl_mulai). Harus unik.')
                                    ->required()
                                    ->unique(ignoreRecord: true, table: Template::class, column: 'form_schema.*.name'), // Unique dalam array form_schema
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
                                        'select' => 'Dropdown (Pilihan)'
                                        // Tambahkan tipe lain sesuai kebutuhan Anda
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('default')
                                    ->label('Nilai Default (Opsional)')
                                    ->nullable(),
                                Forms\Components\Toggle::make('required')
                                    ->label('Wajib Diisi')
                                    ->default(false),
                            ])
                            ->columnSpanFull() // Mengambil lebar penuh dalam section
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null) // Label untuk setiap item di repeater
                            ->grid(2) // Tampilkan item repeater dalam 2 kolom
                            ->default([]), // Default ke array kosong
                    ]),

                Forms\Components\Section::make('Definisi Berkas yang Dibutuhkan')
                    ->description('Tentukan file-file yang perlu diunggah pengguna untuk template ini.')
                    ->schema([
                        Forms\Components\Repeater::make('required_files')
                            ->label('Berkas yang Dibutuhkan')
                            ->helperText('Definisikan nama field (kunci di data_surat.link_files) dan label tampilan.')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Field Berkas (kunci)')
                                    ->helperText('Gunakan snake_case (misal: file_cv, file_transkrip). Harus unik.')
                                    ->required()
                                    ->unique(ignoreRecord: true, table: Template::class, column: 'required_files.*.name'), // Unique dalam array required_files
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Template')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('class_name')
                    ->label('Nama Blade View')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('for_user')
                    ->label('Untuk Pengguna')
                    ->boolean() // Menampilkan ikon centang/silang
                    ->sortable(),
                Stack::make([
                    Tables\Columns\TextColumn::make('form_schema')
                        ->label('Jumlah Field Data')
                        ->getStateUsing(fn (Template $record): string => count($record->form_schema) . ' field(s)')
                        ->badge() // Menampilkan sebagai badge
                        ->color('info'),
                    Tables\Columns\TextColumn::make('form_schema_preview')
                        ->label('Preview Field Data')
                        ->getStateUsing(function (Template $record): string {
                            if (empty($record->form_schema)) {
                                return 'Tidak ada field data.';
                            }
                            // Ambil 3 label pertama dan gabungkan
                            $labels = array_map(fn($item) => $item['label'], array_slice($record->form_schema, 0, 3));
                            return implode(', ', $labels) . (count($record->form_schema) > 3 ? '...' : '');
                        })
                        ->wrap() // Membungkus teks jika terlalu panjang
                        ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall) // Ukuran teks lebih kecil
                        ->color('secondary'),
                ]), // Label untuk kolom Stack ini

                Stack::make([
                    Tables\Columns\TextColumn::make('required_files')
                        ->label('Jumlah Berkas')
                        ->getStateUsing(fn (Template $record): string => count($record->required_files) . ' berkas')
                        ->badge()
                        ->color('warning'),
                    Tables\Columns\TextColumn::make('required_files_preview')
                        ->label('Preview Berkas')
                        ->getStateUsing(function (Template $record): string {
                            if (empty($record->required_files)) {
                                return 'Tidak ada berkas dibutuhkan.';
                            }
                            $labels = array_map(fn($item) => $item['label'], array_slice($record->required_files, 0, 3));
                            return implode(', ', $labels) . (count($record->required_files) > 3 ? '...' : '');
                        })
                        ->wrap()
                        ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                        ->color('secondary'),
                ]), // Label untuk kolom Stack ini

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Bisa disembunyikan secara default
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah Pada')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('for_user')
                    ->label('Tersedia untuk Pengguna')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    // ->nullableLabel('Semua')
                    ,
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
