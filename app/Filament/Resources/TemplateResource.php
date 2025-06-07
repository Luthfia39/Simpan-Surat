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

    protected static ?string $navigationGroup = 'Manajemen Surat';

    protected static ?string $pluralLabel = 'Templates Surat';

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
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('class_name')
                            ->label('Nama Blade View (misal: magang)')
                            ->helperText('Akan merujuk ke resources/views/templates/{classname}.blade.php untuk rendering PDF/UI.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Toggle::make('for_user')
                            ->label('Tersedia untuk Pengguna Umum')
                            ->helperText('Jika aktif, template ini akan muncul di daftar pilihan pengguna.')
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
                                    ->helperText('Gunakan snake_case (misal: nama_lengkap, tgl_mulai). Harus unik.')
                                    ->required()
                                    ->unique(ignoreRecord: true, table: Template::class, column: 'form_schema.*.name'),
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
                                    ])
                                    ->required()
                                    ->live(),

                                // Tambahkan helper text input di sini
                                Forms\Components\TextInput::make('helper_text')
                                    ->label('Teks Bantuan (Opsional)')
                                    ->helperText('Akan muncul di bawah input field.')
                                    ->nullable()
                                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'textarea', 'number', 'date', 'email', 'url', 'select'])), // Tampilkan untuk tipe input tertentu

                                Forms\Components\TextInput::make('default')
                                    ->label('Nilai Default (Opsional)')
                                    ->nullable(),
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
                            ])
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->grid(2)
                            ->default([]),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Template')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->color('primary')
                    ->description(fn (Template $record): string => $record->class_name)
                    ->tooltip(fn (Template $record): string => 'View: ' . $record->class_name),

                Tables\Columns\IconColumn::make('for_user')
                    ->label('Tersedia untuk Pengguna')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (bool $state): string => $state ? 'Tersedia untuk pengguna' : 'Tidak tersedia untuk pengguna')
                    ->sortable(),

                // Kolom untuk Field Data (lebih ekspresif)
                Stack::make([
                    Tables\Columns\TextColumn::make('input_data_label') // <-- Kolom baru sebagai label dalam Stack
                        ->label('Input Data Form') // Ini akan jadi header kolom untuk tampilan Filament
                        ->getStateUsing(fn (): string => 'Input Data Form')
                        ->weight(FontWeight::SemiBold) // Lebih tebal
                        ->color('gray') // Warna netral
                        ->alignCenter(), // Pusatkan teks label
                    Tables\Columns\TextColumn::make('form_schema_count')
                        ->label('Jumlah Field Data')
                        ->getStateUsing(fn (Template $record): string => count($record->form_schema) . ' field(s)')
                        ->badge()
                        ->color(fn (Template $record): string => count($record->form_schema) > 0 ? 'info' : 'gray')
                        ->tooltip(fn (Template $record): string => count($record->form_schema) . ' field input data'),
                    Tables\Columns\TextColumn::make('form_schema_preview')
                        ->label('Preview Field Data')
                        ->getStateUsing(function (Template $record): string {
                            if (empty($record->form_schema)) {
                                return 'Tidak ada field data.';
                            }
                            $labels = array_map(fn($item) => $item['label'], array_slice($record->form_schema, 0, 3));
                            return implode(', ', $labels) . (count($record->form_schema) > 3 ? '...' : '');
                        })
                        ->wrap()
                        ->size(TextColumn\TextColumnSize::ExtraSmall)
                        ->color('secondary')
                        ->html(),
                ])->space(1), // Removed .label() from Stack directly

                // Kolom untuk Berkas Dibutuhkan (lebih ekspresif)
                Stack::make([
                    Tables\Columns\TextColumn::make('berkas_pendukung_label') // <-- Kolom baru sebagai label dalam Stack
                        ->label('Berkas Pendukung') // Ini akan jadi header kolom untuk tampilan Filament
                        ->getStateUsing(fn (): string => 'Berkas Pendukung')
                        ->weight(FontWeight::SemiBold)
                        ->color('gray')
                        ->alignCenter(),
                    Tables\Columns\TextColumn::make('required_files_count')
                        ->label('Jumlah Berkas')
                        ->getStateUsing(fn (Template $record): string => count($record->required_files) . ' berkas')
                        ->badge()
                        ->color(fn (Template $record): string => count($record->required_files) > 0 ? 'warning' : 'gray')
                        ->tooltip(fn (Template $record): string => count($record->required_files) . ' berkas yang dibutuhkan'),
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
                        ->size(TextColumn\TextColumnSize::ExtraSmall)
                        ->color('secondary')
                        ->html(),
                ])->space(1), // Removed .label() from Stack directly

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('info')
                    ->tooltip(fn (Template $record): string => 'Dibuat: ' . $record->created_at?->format('d M Y H:i:s')),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('for_user')
                    ->label('Tersedia untuk Pengguna')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    // ->nullableLabel('Semua')
                    ->indicator('Status Pengguna'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->icon('heroicon-s-eye')->color('secondary'),
                Tables\Actions\EditAction::make()->icon('heroicon-s-pencil-square')->color('primary'),
                Tables\Actions\DeleteAction::make()->icon('heroicon-s-trash')->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
                ->label('Aksi Massal Template')
                ->icon('heroicon-m-rectangle-stack')
                ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc');
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
