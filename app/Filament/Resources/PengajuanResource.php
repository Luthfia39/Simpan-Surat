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

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Repeater;
use Carbon\Carbon;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationLabel = 'Pengajuan';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationBadgeTooltip = 'Jumlah Pengajuan yang Belum Selesai';

    public static function getNavigationBadge(): ?string
    {
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();
        $count = 0;

        if ($user->is_admin) {
            $count = \App\Models\Pengajuan::where('status', 'pending')->count();
        } else {
            $count = \App\Models\Pengajuan::where('user_id', $user->_id)
                                          ->whereIn('status', ['diproses', 'selesai', 'ditolak'])
                                          ->count();
        }

        return $count > 0 ? (string) $count : null; // Tampilkan badge jika jumlah > 0
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();

        if ($user->is_admin) {
            $count = \App\Models\Pengajuan::where('status', 'pending')->count();
            return $count > 0 ? 'danger' : 'gray'; 
        } else {
            $count = \App\Models\Pengajuan::where('user_id', $user->_id)
                                          ->whereIn('status', ['diproses', 'selesai', 'ditolak'])
                                          ->count();
            return $count > 0 ? 'success' : 'gray'; 
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Pengajuan')
                            ->schema([

                                Forms\Components\Select::make('user_id')
                                    ->label('Pengaju')
                                    ->relationship('user', 'name')
                                    ->default(Auth::user()->id)
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(),

                                    Select::make('template_id')
                                    ->label('Pilih Jenis Surat')
                                    ->relationship(
                                        name: 'template', 
                                        titleAttribute: 'name', 
                                        modifyQueryUsing: fn (Builder $query) => $query->where('for_user', true),
                                    )
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('data_surat', []); 
                                        $set('data_surat.link_files', []);
                                    })
                                    ->disabled(Auth::user()->is_admin) 
                                    ->searchable()
                                    ->preload(),
                            ])->columns(2),

                        Forms\Components\Section::make('Input Data Surat')
                            ->description('Isi data spesifik untuk jenis surat yang dipilih.')
                            ->schema(function (Get $get, ?\App\Models\Pengajuan $record): array {
                                $templateId = $get('template_id');
                                if (!$templateId) {
                                    return [
                                        Forms\Components\Placeholder::make('no_template_selected_data')
                                            ->content('Pilih jenis surat terlebih dahulu untuk mengisi data surat.'),
                                    ];
                                }

                                $template = Template::find($templateId);
                                $fields = [];
                                if ($template && is_array($template->form_schema)) {
                                    foreach ($template->form_schema as $fieldConfig) {
                                        $fieldName = 'data_surat.' . $fieldConfig['name'];

                                        $filamentComponent = null;

                                        if ($fieldConfig['name'] === 'nim' && class_exists(NimInput::class)) { // Cek NimInput ada
                                            $filamentComponent = NimInput::make($fieldName)
                                                ->validationAttribute('NIM')
                                                ->format();
                                        } elseif ($fieldConfig['name'] === 'ipk' && class_exists(IpkInput::class)) { // Cek IpkInput ada
                                            $filamentComponent = IpkInput::make($fieldName)
                                                ->validationAttribute('IPK')
                                                ->format();
                                        } elseif ($fieldConfig['name'] === 'thn_akademik') {
                                            $filamentComponent = TextInput::make($fieldName)->mask('9999/9999');
                                            // $filamentComponent = TextInput::make($fieldName)
                                            //     ->mask('9999/9999')
                                            //     ->format()
                                                // ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                //     // Pastikan state tidak kosong dan mengandung '/'
                                                //     if ($state && Str::contains($state, '/')) {
                                                //         $parts = explode('/', $state);
                                                //         $firstYear = (int) $parts[0];
                                                //         // Jika angka pertama valid dan kita sudah punya slash
                                                //         if ($firstYear > 1900 && strlen($parts[0]) === 4) { // Cek tahun masuk akal
                                                //             $nextYear = $firstYear + 1;
                                                //             // Set kembali nilai field dengan tahun kedua yang otomatis
                                                //             $set($get('statePath'), $firstYear . '/' . $nextYear);
                                                //         }
                                                //     }
                                                // })
                                                // ->live(onBlur: true)
                                                // ;
                                        } elseif ($fieldConfig['name'] === 'nip') {
                                            $filamentComponent = TextInput::make($fieldName)->minLength(18)->mask('999999999999999999');
                                        } elseif ($fieldConfig['name'] === 'nomor_surat') {
                                            $filamentComponent = TextInput::make($fieldName)->numeric()->minValue(1);
                                        } 
                                        
                                        elseif ($fieldConfig['type'] === 'repeater') {
                                            // Jika tipe adalah 'repeater', kita akan membuat Repeater baru
                                            $subFieldsSchema = [];
                                            // Iterasi melalui `sub_schema` yang didefinisikan di Template
                                            foreach ($fieldConfig['sub_schema'] ?? [] as $subFieldConfig) {
                                                $subFieldName = $fieldName . '.' . $subFieldConfig['name']; // Path untuk sub-field (misal: data_surat.kelompok.0.nama)
                                                $subFilamentComponent = null;

                                                // Penanganan tipe input untuk sub-field
                                                switch ($subFieldConfig['type']) {
                                                    case 'textarea':
                                                        $subFilamentComponent = Textarea::make($subFieldName);
                                                        break;
                                                    case 'number':
                                                        $subFilamentComponent = TextInput::make($subFieldName)->numeric();
                                                        break;
                                                    case 'date':
                                                        $subFilamentComponent = DatePicker::make($subFieldName);
                                                        break;
                                                    case 'select':
                                                        // Handle select options for sub-field if needed
                                                        $subOptions = collect($subFieldConfig['options'] ?? [])->mapWithKeys(function ($option) {
                                                            return [$option['value'] => $option['label']];
                                                        })->toArray();
                                                        $subFilamentComponent = Select::make($subFieldName)->options($subOptions);
                                                        break;
                                                    case 'text':
                                                    default:
                                                        $subFilamentComponent = TextInput::make($subFieldName);
                                                        break;
                                                }

                                                if ($subFieldConfig['name'] === 'nim') { // Cek NimInput ada
                                                    $subFilamentComponent = TextInput::make($subFieldName)
                                                        ->placeholder('00/000000/SV/00000')
                                                        ->mask('99/999999/aa/99999')
                                                        ->regex('/^\d{2}\/\d{6}\/[A-Z]{2}\/\d{5}$/');
                                                        // dd($subFilamentComponent);
                                                } elseif ($subFieldConfig['name'] === 'ipk' && class_exists(IpkInput::class)) { // Cek IpkInput ada
                                                    $subFilamentComponent = IpkInput::make($subFieldName)
                                                        ->validationAttribute('IPK')
                                                        ->format();
                                                } elseif ($subFieldConfig['name'] === 'thn_akademik') {
                                                    // $subFilamentComponent = TextInput::make($subFieldName)->mask('9999/9999');
                                                    $filamentComponent = TextInput::make($fieldName)
                                                        ->mask('9999/9999')
                                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                            // Pastikan state tidak kosong dan mengandung '/'
                                                            if ($state && Str::contains($state, '/')) {
                                                                $parts = explode('/', $state);
                                                                $firstYear = (int) $parts[0];
                                                                // Jika angka pertama valid dan kita sudah punya slash
                                                                if ($firstYear > 1900 && strlen($parts[0]) === 4) { // Cek tahun masuk akal
                                                                    $nextYear = $firstYear + 1;
                                                                    // Set kembali nilai field dengan tahun kedua yang otomatis
                                                                    $set($get('statePath'), $firstYear . '/' . $nextYear);
                                                                }
                                                            }
                                                        })
                                                        ->live(onBlur: true);
                                                } elseif ($subFieldConfig['name'] === 'nip') {
                                                    $subFilamentComponent = TextInput::make($subFieldName)->minLength(18)->mask('999999999999999999');
                                                } 

                                                if ($subFilamentComponent) {
                                                    $subFilamentComponent
                                                        ->label($subFieldConfig['label'])
                                                        ->default($subFieldConfig['default'] ?? null)
                                                        ->helperText(new HtmlString($subFieldConfig['helper_text'] ?? null))
                                                        ->required($subFieldConfig['required'] ?? false);
                                                        
                                                    $subFieldsSchema[] = $subFilamentComponent;
                                                }
                                            }

                                            $filamentComponent = Repeater::make($fieldName)
                                                ->label($fieldConfig['label'])
                                                ->schema($subFieldsSchema)
                                                ->addActionLabel($fieldConfig['add_action_label'] ?? 'Tambah Item') // Label untuk tombol tambah repeater
                                                ->reorderableWithButtons() // Agar bisa diurutkan ulang
                                                ->columns(2) // Jumlah kolom dalam repeaters
                                                ->columnSpan('full') // Agar repeater memenuhi lebar
                                                ->maxItems($fieldConfig['max_items'] ?? null) // Batasan jumlah item
                                                ->required($fieldConfig['required'] ?? false)
                                                ->default($fieldConfig['default'] ?? []); // Default untuk repeater adalah array kosong
                                        } else {
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
                                            $filamentComponent
                                                ->label($fieldConfig['label'])
                                                ->default($fieldConfig['default'] ?? null);

                                            if (!empty($fieldConfig['helper_text'])) {
                                                $filamentComponent->helperText(new HtmlString($fieldConfig['helper_text']));
                                            }

                                            if ($fieldConfig['required'] ?? false) {
                                                $filamentComponent->required();
                                            }

                                            if (in_array($fieldConfig['name'], ['nomor_surat'])) {
                                                $filamentComponent->visible(fn () => auth()->user()->is_admin);
                                            }

                                            $fields[] = $filamentComponent;
                                        }
                                    }
                                }
                                if (empty($fields)) {
                                    return [
                                        Forms\Components\Placeholder::make('no_data_fields_required')
                                            ->content('Jenis surat ini tidak memerlukan input data spesifik.'),
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
                                            ->content('Pilih jenis surat terlebih dahulu untuk melihat form upload file.'),
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
                                            ->openable()
                                            ->required($fileConfig['required'] ?? false);
                                    }
                                }

                                if (empty($fields)) {
                                    return [
                                        Forms\Components\Placeholder::make('no_files_required')
                                            ->content('Jenis surat ini tidak memerlukan berkas yang diupload.'),
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
                                            'menunggu_ttd' => 'Menunggu Tanda Tangan',
                                            'selesai' => 'Selesai',
                                            'ditolak' => 'Ditolak',
                                        ])
                                        ->required()
                                        ->default('pending'),
                                    Forms\Components\Textarea::make('keterangan')
                                        ->label('Keterangan')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->visible(fn (string $operation) => Auth::user()->is_admin || $operation === 'view'),
                            Forms\Components\Section::make('Berkas Surat Keluar Final')
                                ->schema([
                                    Forms\Components\Placeholder::make('surat_keluar_status')
                                        ->label('')
                                        ->content(function (?\App\Models\Pengajuan $record) {
                                            if ($record && ($record->suratKeluar || ($record->suratKeluar && !empty($record->suratKeluar))) && $record->status === 'menunggu_ttd') {
                                                return 'Mohon ditunggu, surat sedang proses penandatanganan.';
                                            }
                                            if ($record && ($record->suratKeluar || ($record->suratKeluar && !empty($record->suratKeluar))) && $record->status === 'selesai') {
                                                return 'Surat telah tersedia.';
                                            }
                                            return 'Belum ada surat keluar.';
                                        })
                                        ->visibleOn('view')
                                        ->hiddenOn('create'),

                                    // FileUpload untuk mengupload surat yang sudah ditandatangani
                                    FileUpload::make('suratKeluar.pdf_url') // Langsung binding ke relasi suratKeluar dan kolom pdf_url
                                        ->label('Upload Surat Keluar Final (Sudah Ditandatangani)')
                                        ->helperText('Unggah versi final surat yang sudah ditandatangani basah disini.')
                                        ->directory('surat_keluar') // Direktori baru untuk PDF final
                                        ->visibility('public')
                                        ->acceptedFileTypes(['application/pdf']) 
                                        ->maxSize(5120) 
                                        ->preserveFilenames() 
                                        ->downloadable()
                                        ->openable()
                                        // Visible hanya jika statusnya 'diproses' atau 'menunggu_ttd' dan ada surat yang digenerate
                                        ->visible(function (Get $get, ?\App\Models\Pengajuan $record): bool {
                                            $suratKeluarExists = $record && $record->suratKeluar && $record->suratKeluar->exists;
                                            $status = $get('status');
                                            return auth()->user()->is_admin && in_array($status, ['menunggu_ttd']) && $suratKeluarExists;
                                        }),

                                    // Aksi download PDF yang sudah diupload/generate
                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('downloadFinalPdf') // Ubah nama aksi
                                            ->label('Download PDF Surat Final')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->color('primary')
                                            ->url(function (?\App\Models\Pengajuan $record): string {
                                                if ($record && $record->suratKeluar && $record->suratKeluar->pdf_url) {
                                                    return asset('/storage/surat_keluar/' . $record->suratKeluar->pdf_url);
                                                }
                                                return '#';
                                            })
                                            ->openUrlInNewTab()
                                            ->visible(function (?\App\Models\Pengajuan $record, string $operation): bool {
                                                // Tampilkan tombol download jika ada URL PDF di suratKeluar
                                                return $record && $record->suratKeluar->is_show && $operation === 'view' && $record->status === 'selesai';
                                            }),
                                    ])
                                    ->columnSpanFull(), 
                                ])
                                ->columns(1) // Atur kolom untuk section Berkas Surat Keluar Final menjadi 1
                                ->visible(fn (string $operation) => Auth::user()->is_admin || $operation === 'view'), // Tampilkan section ini hanya untuk admin atau saat view
                        ])
                        ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengaju')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('template.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'primary' => 'pending',
                        'warning' => 'diproses',
                        'success' => 'selesai',
                        'danger' => 'ditolak',
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_surat.nama')
                    ->getStateUsing(fn (Pengajuan $record): string => $record->data_surat['nama'] ?? '-')
                    ->label('Nama Pengaju (Surat)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->getStateUsing(function ($record): ?string {
                        // Jika created_at itu adalah kolom timestamp langsung dari DB
                        if ($record->created_at instanceof \DateTimeInterface) {
                            // Menggunakan Carbon untuk format lokal
                            return Carbon::parse($record->created_at)->locale('id')->translatedFormat('l, j F Y');
                        }
                        // Jika tanggal ada di extracted_fields (misal 'tanggal' dari OCR)
                        // dan kamu ingin menggunakan itu, pastikan itu sudah string tanggal yang valid.
                        if (is_array($record->extracted_fields) && isset($record->extracted_fields['tanggal']['text'])) {
                            try {
                                return Carbon::parse($record->extracted_fields['tanggal']['text'])->locale('id')->translatedFormat('l, j F Y');
                            } catch (\Exception $e) {
                                // Jika parsing gagal, kembalikan teks aslinya atau null
                                return $record->extracted_at['tanggal']['text'] ?? null;
                            }
                        }
                        return '-'; // Default jika tidak ada data
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('template_name') 
                    ->label('Jenis Surat')
                    ->options(
                        Template::all()->pluck('name', '_id')->toArray() 
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && !empty($data['value'])) {
                            $templateId = $data['value']; 
                            $query->where('template_id', $templateId);
                        }
                        return $query;
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query): Builder {
                // Periksa apakah pengguna yang sedang login adalah admin
                if (Auth::user() && !Auth::user()->is_admin) {
                    // Jika bukan admin, filter berdasarkan user_id pengajuan
                    $query->where('user_id', auth()->user()->id);
                }
                return $query;
            })
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Buat Surat Keluar')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('success')
                    ->visible(fn (\App\Models\Pengajuan $record): bool =>
                        auth()->user()->is_admin &&
                        ($record->status !== 'selesai' && $record->status !== 'ditolak')
                    ),
            ])
            ->bulkActions([ ]);
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
