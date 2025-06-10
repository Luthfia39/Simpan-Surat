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

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationLabel = 'Pengajuan';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        // Pastikan pengguna sudah login sebelum melakukan query
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();
        $count = 0;

        if ($user->is_admin) {
            // Admin: Tampilkan jumlah pengajuan yang pending (baru masuk)
            $count = \App\Models\Pengajuan::where('status', 'pending')->count();
        } else {
            // Pengguna biasa: Tampilkan jumlah pengajuan yang statusnya berubah (selain pending)
            $count = \App\Models\Pengajuan::where('user_id', $user->_id)
                                          ->whereIn('status', ['diproses', 'selesai', 'ditolak'])
                                          ->count();
        }

        return $count > 0 ? (string) $count : null; // Tampilkan badge jika jumlah > 0
    }

    public static function getNavigationBadgeColor(): ?string
    {
        // Pastikan pengguna sudah login sebelum menentukan warna
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();

        if ($user->is_admin) {
            // Warna badge untuk admin (pending)
            $count = \App\Models\Pengajuan::where('status', 'pending')->count();
            return $count > 0 ? 'danger' : 'gray'; // Merah jika ada, abu-abu jika tidak ada
        } else {
            // Warna badge untuk pengguna biasa (berubah)
            $count = \App\Models\Pengajuan::where('user_id', $user->_id)
                                          ->whereIn('status', ['diproses', 'selesai', 'ditolak'])
                                          ->count();
            return $count > 0 ? 'success' : 'gray'; // Hijau jika ada, abu-abu jika tidak ada
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

                                Forms\Components\Select::make('template_id')
                                    ->label('Pilih Template Surat')
                                    ->relationship('template', 'name')
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
                                            $filamentComponent = NimInput::make($fieldName)
                                            ->validationAttribute('NIM')
                                            ->format(); // Memanggil custom component NimInput
                                        } elseif ($fieldConfig['name'] === 'ipk') {
                                            $filamentComponent = IpkInput::make($fieldName)
                                            ->validationAttribute('IPK')
                                            ->format(); // Memanggil custom component IpkInput
                                        } elseif ($fieldConfig['name'] === 'thn_akademik') {
                                            $filamentComponent = TextInput::make($fieldName)
                                            ->mask('9999/9999');
                                        } elseif ($fieldConfig['name'] === 'nip') {
                                            $filamentComponent = TextInput::make($fieldName)
                                            ->minLength(18)
                                            ->mask('999999999999999999');
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
                                            $filamentComponent
                                                ->label($fieldConfig['label'])
                                                ->default($fieldConfig['default'] ?? null);

                                            if (!empty($fieldConfig['helper_text'])) {
                                                $filamentComponent->helperText($fieldConfig['helper_text']);
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
                                ])
                                ->columns(2)
                                ->visible(fn (string $operation) => Auth::user()->is_admin || $operation === 'view'),
                            Forms\Components\Section::make('Berkas Surat')
                                ->schema([
                                    Forms\Components\Placeholder::make('surat_keluar_status')
                                        ->label('')
                                        ->content(function (?\App\Models\Pengajuan $record) {
                                            if ($record && ($record->suratKeluar || ($record->suratKeluar && !empty($record->suratKeluar)))) {
                                                return 'Sudah ada surat keluar.';
                                            }
                                            return 'Belum ada surat keluar.';
                                        })
                                        ->visibleOn('view') // Hanya terlihat di halaman view (bukan create)
                                        ->hiddenOn('create'), // Menyembunyikan di halaman create
    
                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('downloadPdf')
                                            ->label('Download PDF Surat')
                                            ->icon('heroicon-o-arrow-down-tray') // Menggunakan ikon outline
                                            ->color('primary')
                                            ->url(function (?\App\Models\Pengajuan $record): string {
                                                $pdfUrl = '#'; // Default atau fallback URL
                                                if ($record) {
                                                    $suratKeluarData = null;
                                                    // Coba akses melalui relasi BelongsTo terlebih dahulu
                                                    if ($record->relationLoaded('suratKeluar') && $record->suratKeluar) {
                                                        $suratKeluarData = $record->suratKeluar;
                                                    }
                                                    // Jika tidak ada relasi atau relasi null, coba cek data embedded
                                                    elseif (isset($record->suratKeluar) && is_array($record->suratKeluar)) {
                                                        $suratKeluarData = $record->suratKeluar;
                                                    }
    
                                                    if ($suratKeluarData) {
                                                        // Ambil pdf_url baik dari object (relasi) atau array (embedded)
                                                        $pdfUrl = $suratKeluarData instanceof \Jenssegers\Mongodb\Eloquent\Model ? $suratKeluarData->pdf_url : ($suratKeluarData['pdf_url'] ?? '#');
                                                    }
                                                }
                                                return 'http://127.0.0.1:8000/storage/surat_keluar/'.$pdfUrl; // Mengembalikan URL lengkap
                                            })
                                            ->openUrlInNewTab()
                                            ->visible(function (?\App\Models\Pengajuan $record) {
                                                // Tombol hanya terlihat jika ada record dan surat keluar sudah ada
                                                if ($record && $record->getKey()) {
                                                    // Cek apakah surat keluar sudah ada dan memiliki PDF URL yang valid
                                                    $suratKeluarData = null;
                                                    if ($record->relationLoaded('suratKeluar') && $record->suratKeluar) {
                                                        $suratKeluarData = $record->suratKeluar;
                                                    } elseif (isset($record->suratKeluar) && is_array($record->suratKeluar)) {
                                                        $suratKeluarData = $record->suratKeluar;
                                                    }
                                                    return $suratKeluarData && ($suratKeluarData instanceof \Jenssegers\Mongodb\Eloquent\Model ? !empty($suratKeluarData->pdf_url) : !empty($suratKeluarData['pdf_url']));
                                                }
                                                return false;
                                            }),
                                    ])->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->visibleOn('view')
                                ->hiddenOn('create'),
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
                    ->label('Nama Pengaju (Surat)')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (\App\Models\Pengajuan $record): bool =>
                        auth()->user()->is_admin &&
                        ($record->status !== 'selesai' && $record->status !== 'ditolak')
                    ),
                Tables\Actions\Action::make('createSuratKeluar')
                    ->label('Buat Surat Keluar')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Buat Surat Keluar?')
                    ->modalDescription('Ini akan membuat dokumen surat keluar dan memperbarui status pengajuan.')
                    ->modalSubmitActionLabel('Konfirmasi Buat Surat')
                    ->action(function (Pengajuan $record) {
                        try {
                            $userData = $record->user;
                            $templateData = $record->template;
                            $dataSurat = $record->data_surat; // Ini adalah array PHP dari data input template
    
                            if (!$userData || !$templateData) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Pengajuan tidak memiliki pengguna atau template yang valid.')
                                    ->danger()
                                    ->send();
                                return;
                            }
    
                            // Prioritaskan nomor surat dari admin yang diisi di data_surat
                            // Jika tidak ada, fallback ke generate otomatis
                            $nomorSurat = 'NO.'. $dataSurat['nomor_surat']  . '/UN1/SV2-TEDI/AKM/PJ/'. date("Y") ;
                            $prodiUser = $userData->major['kode'] ?? 'N/A'; // Prodi dari data user
    
                            $pdfPath = null;
                            try {
                                // --- KUNCI PERUBAHAN DI SINI ---
                                // Bongkar array $dataSurat menjadi variabel individual untuk Blade
                                $viewData = [
                                    'pengajuan' => $record,
                                    'user' => $userData,
                                    'template' => $templateData,
                                    'linkFiles' => $dataSurat['link_files'] ?? [] // Link file yang diupload
                                ];
    
                                // Tambahkan semua isi $dataSurat langsung ke $viewData
                                // Ini akan membuat $nama, $nim, $prodi (dari data_surat), $nama_ortu, dll., tersedia
                                foreach ($dataSurat as $key => $value) {
                                    // Hindari menimpa variabel yang sudah ada seperti 'linkFiles'
                                    if (!in_array($key, ['link_files'])) {
                                        $viewData[$key] = $value;
                                    }
                                }
                                // Pastikan variabel $prodi di Blade sesuai dengan yang di-input admin
                                // Jika $dataSurat punya 'prodi', gunakan itu. Jika tidak, gunakan dari user.
                                $viewData['prodi'] = $dataSurat['prodi'] ?? $prodiUser;
    
    
                                $pdfContent = view('templates.' . $templateData->class_name, $viewData)->render();
    
                                $pdfFileName = 'surat_keluar_' . Str::slug($templateData->name) . '_' . Str::slug($dataSurat['nama'] ?? 'unknown') . '_' . time() . '.pdf';
                                Storage::disk('public')->put('surat_keluar/' . $pdfFileName, Pdf::loadHTML($pdfContent)->output());
                                // $pdfUrl = Storage::disk('public')->url('surat_keluar/' . $pdfFileName);
    
                            } catch (\Exception $e) {
                                 Notification::make()
                                    ->title('Gagal Membuat PDF')
                                    ->body('Terjadi kesalahan saat merender PDF: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                                return;
                            }
    
                            // Buat record SuratKeluar baru
                            $suratKeluar = \App\Models\SuratKeluar::create([
                                'nomor_surat' => $nomorSurat,
                                'prodi' => $prodiUser, // Ini adalah prodi yang tersimpan di SuratKeluar (dari user)
                                'pdf_url' => $pdfFileName,
                                'template_id' => $templateData->_id,
                                'pengajuan_id' => $record->_id,
                                'metadata' => $dataSurat // Tetap simpan semua data_surat sebagai metadata
                            ]);
    
                            // Perbarui Pengajuan dengan ID Surat Keluar dan Status (jika belum selesai)
                            $updateData = [
                                'surat_keluar_id' => $suratKeluar->_id,
                            ];
                            if ($record->status !== 'selesai') {
                                $updateData['status'] = 'selesai';
                            }
                            $record->update($updateData);
    
                            Notification::make()
                                ->title('Surat Keluar Berhasil Dibuat')
                                ->body('Nomor Surat: ' . $nomorSurat . ' telah dibuat. <a href="' . $pdfUrl . '" target="_blank" class="underline">Lihat PDF</a>')
                                ->success()
                                ->send();
    
                            return redirect()->route('filament.admin.resources.pengajuans.edit', ['record' => $record->getKey()]);
    
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Membuat Surat Keluar')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (\App\Models\Pengajuan $record): bool => $record->status !== 'selesai' && !$record->surat_keluar_id && Auth::user()->is_admin),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tidak ada bulk delete sesuai policy
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
