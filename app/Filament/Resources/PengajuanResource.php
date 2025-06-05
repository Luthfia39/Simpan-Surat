<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Models\Pengajuan;
use App\Models\Template;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Forms\Components\NimInput;
use App\Forms\Components\IpkInput;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationLabel = 'Pengajuan';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Pengajuan')
                            ->schema([
                                Forms\Components\TextInput::make('user_id')
                                    ->label('Pengaju')
                                    ->default(Auth::user()->name)
                                    ->required(),

                                Forms\Components\Select::make('template_id')
                                    ->label('Pilih Template Surat')
                                    ->relationship('template', 'name')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('data_surat', []);
                                        $set('data_surat.link_files', []);
                                    })
                                    ->searchable()
                                    ->preload(),
                            ])->columns(2),

                        Forms\Components\Section::make('Input Data Surat')
                            ->description('Isi data spesifik untuk template yang dipilih.')
                            ->schema(function (Get $get, ?\App\Models\Pengajuan $record): array {
                                $templateId = $get('template_id');
                                if (!$templateId) {
                                    return [
                                        Forms\Components\Placeholder::make('no_template_selected_data')
                                            ->content('Pilih template terlebih dahulu untuk mengisi data surat.'),
                                    ];
                                }

                                $template = Template::find($templateId);
                                $fields = [];
                                if ($template && is_array($template->form_schema)) {
                                    foreach ($template->form_schema as $fieldConfig) {
                                        $fieldName = 'data_surat.' . $fieldConfig['name'];

                                        $filamentComponent = null;

                                        // --- Panggil Custom Component berdasarkan 'name' fieldConfig ---
                                        if ($fieldConfig['name'] === 'nim') {
                                            $filamentComponent = NimInput::make($fieldName); // Memanggil custom component NimInput
                                        } elseif ($fieldConfig['name'] === 'ipk') {
                                            $filamentComponent = IpkInput::make($fieldName); // Memanggil custom component IpkInput
                                        } else {
                                            // Panggil component Filament bawaan berdasarkan 'type'
                                            switch ($fieldConfig['type']) {
                                                case 'textarea':
                                                    $filamentComponent = Textarea::make($fieldName);
                                                    break;
                                                case 'number':
                                                    $filamentComponent = TextInput::make($fieldName)->numeric();
                                                    break;
                                                case 'date':
                                                    $filamentComponent = DatePicker::make($fieldName);
                                                    break;
                                                case 'select':
                                                    $options = collect($fieldConfig['options'] ?? [])->mapWithKeys(function ($option) {
                                                        return [$option['value'] => $option['label']];
                                                    })->toArray();
                                                    $filamentComponent = Select::make($fieldName)
                                                        ->options($options);
                                                    break;
                                                case 'text':
                                                default:
                                                    $filamentComponent = TextInput::make($fieldName);
                                                    break;
                                            }
                                        }


                                        if ($filamentComponent) {
                                            // Apply label dan required dari template
                                            // Label override dari custom component akan tetap berlaku jika didefinisikan di sana
                                            $filamentComponent
                                                ->label($fieldConfig['label'])
                                                ->default($fieldConfig['default'] ?? null);

                                            if ($fieldConfig['required'] ?? false) {
                                                $filamentComponent->required();
                                            }
                                            $fields[] = $filamentComponent;
                                        }
                                    }
                                }
                                if (empty($fields)) {
                                    return [
                                        Forms\Components\Placeholder::make('no_data_fields_required')
                                            ->content('Template ini tidak memerlukan input data spesifik.'),
                                    ];
                                }
                                return $fields;
                            })->columns(2),

                        Forms\Components\Section::make('Upload Berkas Pendukung')
                            ->schema(function (Get $get, ?\App\Models\Pengajuan $record): array {
                                $templateId = $get('template_id');
                                if (!$templateId) {
                                    return [
                                        Forms\Components\Placeholder::make('no_template_selected_files')
                                            ->content('Pilih template terlebih dahulu untuk melihat form upload file.'),
                                    ];
                                }

                                $template = Template::find($templateId);
                                $fields = [];
                                if ($template && is_array($template->required_files)) {
                                    foreach ($template->required_files as $fileConfig) {
                                        $fieldName = 'data_surat.link_files.' . $fileConfig['name'];
                                        $fields[] = FileUpload::make($fieldName)
                                            ->label($fileConfig['label'])
                                            ->directory('pengajuan_files')
                                            ->visibility('public')
                                            ->preserveFilenames()
                                            ->downloadable()
                                            ->openable();
                                    }
                                }

                                if (empty($fields)) {
                                    return [
                                        Forms\Components\Placeholder::make('no_files_required')
                                            ->content('Template ini tidak memerlukan berkas yang diupload.'),
                                    ];
                                }
                                return $fields;
                            })
                            ->columns(1),

                        Forms\Components\Section::make('Status dan Keterangan')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status Pengajuan')
                                    ->options([
                                        'pending' => 'Pending',
                                        'diproses' => 'Diproses',
                                        'selesai' => 'Selesai',
                                        'ditolak' => 'Ditolak',
                                    ])
                                    ->required()
                                    ->default('pending'),
                                Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                                Forms\Components\Placeholder::make('surat_keluar_info')
                                    ->label('Surat Keluar')
                                    ->content(function (?\App\Models\Pengajuan $record) {
                                        if ($record && isset($record->surat_keluar) && $record->surat_keluar) {
                                            return 'Nomor: ' . ($record->surat_keluar['nomor_surat'] ?? '-') .
                                                   '<br>URL: <a href="' . ($record->surat_keluar['pdf_url'] ?? '#') . '" target="_blank">Download</a>';
                                        }
                                        return 'Belum ada surat keluar.';
                                    })
                                    ->visibleOn('view')
                                    ->hiddenOn('create'),
                            ])
                            ->columns(2)
                            ->hidden(!Auth::user()->is_admin),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
