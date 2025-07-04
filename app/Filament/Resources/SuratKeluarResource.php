<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratKeluarResource\Pages;
use App\Filament\Resources\TemplateResource\Pages as Templates;
use App\Filament\Resources\SuratKeluarResource\RelationManagers;
use App\Models\SuratKeluar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Get;
use Filament\Forms\Set;

use App\Enums\Major;
use Carbon\Carbon;

class SuratKeluarResource extends Resource
{
    protected static ?string $model = SuratKeluar::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationLabel = 'Surat Keluar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('No')
                    ->rowIndex()
                    ->alignCenter(),

                TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('template.name')
                    ->label('Template')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('metadata.prodi')
                    ->label('Program Studi')
                    ->getStateUsing(function ($record) {
                        $prodiCode = null;
                        if (is_array($record->metadata)) {
                            $prodiCode = $record->metadata['prodi'] ?? null;
                        }
                        elseif (is_object($record->metadata)) {
                            $prodiCode = $record->metadata->prodi ?? null;
                        }
                        elseif (is_string($record->metadata)) {
                            $decodedMetadata = json_decode($record->metadata, true);
                            $prodiCode = $decodedMetadata['prodi'] ?? null; 
                        }
                        
                        if ($prodiCode !== null) {
                            return Major::getNameByCode($prodiCode) ? $prodiCode : '-';
                        }
                        return '-';
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Waktu Pembuatan')
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
                        return '-'; 
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->options(
                        array_merge(
                            ['' => 'Semua Program Studi'],
                            array_filter(Major::toArray(), function($value, $key) {
                                return $key !== null && $key !== '' && $value !== null && $value !== '';
                            }, ARRAY_FILTER_USE_BOTH)
                        )
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && !empty($data['value'])) {
                            $filterValue = trim($data['value']);
                            $query->where('metadata', 'regex', '/(?i).*"prodi":"' . preg_quote($filterValue, '/') . '".*/');
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('markAsSelesaiWithUpload')
                    ->label('Tandai Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Tandai Pengajuan Selesai?')
                    ->modalDescription('Unggah surat final yang sudah ditandatangani dan tambahkan keterangan. Ini akan mengubah status pengajuan terkait.')
                    ->form([
                        Forms\Components\FileUpload::make('final_signed_letter')
                            ->label('Unggah Surat Final')
                            ->helperText('Unggah versi final surat yang sudah ditandatangani basah (PDF).')
                            // ->directory('surat_keluar') 
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) 
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
                            ->required(fn (Forms\Get $get): bool => $get('completion_method') === 'online_upload')
                            ->visible(fn (Forms\Get $get): bool => $get('completion_method') === 'online_upload'), 
                        
                        Forms\Components\Textarea::make('keterangan_final')
                            ->label('Keterangan Final')
                            ->helperText('Tambahkan keterangan tambahan terkait penyelesaian pengajuan. (Contoh: "Surat dapat diambil mulai 5 Juli 2025")')
                            ->maxLength(65535)
                            ->required(fn (Forms\Get $get): bool => $get('completion_method') === 'offline_pickup')
                            ->nullable(fn (Forms\Get $get): bool => $get('completion_method') === 'online_upload'),
                        
                        // Dropdown metode penyelesaian
                        Select::make('completion_method')
                            ->label('Metode Penyelesaian')
                            ->options([
                                'online_upload' => 'Unggah Surat Final (Online)',
                                'offline_pickup' => 'Ambil Surat di Ruang Akademik (Offline)',
                            ])
                            ->default('online_upload')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state === 'offline_pickup') {
                                    $set('final_signed_letter', null); 
                                }
                            }),
                    ])
                    ->action(function (array $data, SuratKeluar $record) { 
                        try {
                            $isOfflinePickup = $data['completion_method'] === 'offline_pickup';
                            $uploadedFilePath = $data['final_signed_letter'] ?? null; 

                            if ($isOfflinePickup) {
                                $record->pengajuan->keterangan = $data['keterangan_final'];
                                $record->is_show = false;
                            } else { 
                                Storage::disk('public')->delete('surat_keluar/' . $record->pdf_url);
                                $originalFileName = pathinfo($uploadedFilePath, PATHINFO_FILENAME);
                                $fileExtension = pathinfo($uploadedFilePath, PATHINFO_EXTENSION);
                                $newSignedFileName = $originalFileName . '_signed.' . $fileExtension;
                                Storage::disk('public')->move($uploadedFilePath, 'surat_keluar/' . $newSignedFileName);

                                $record->pdf_url = $newSignedFileName; 
                                $record->is_show = true; 
                            }
                            $record->save(); 

                            // Update status dan keterangan di model Pengajuan (melalui relasi)
                            $pengajuanTerkait = $record->pengajuan;
                            if ($pengajuanTerkait) {
                                $pengajuanTerkait->status = 'selesai';
                                $pengajuanTerkait->keterangan = $data['keterangan_final'];
                                $pengajuanTerkait->save();
                                Notification::make()->title('Pengajuan Berhasil Ditandai Selesai')->body('Surat final berhasil diunggah/dicatat dan pengajuan telah ditandai selesai.')->success()->send();
                            } else {
                                Notification::make()->title('Peringatan')->body('Surat final berhasil diunggah/dicatat, tetapi tidak ada pengajuan terkait yang ditemukan untuk diupdate statusnya.')->warning()->send();
                            }

                        } catch (\Throwable $e) {
                            \Log::error('Error menandai selesai dengan upload di SuratKeluarResource:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'surat_keluar_id' => $record->id]);
                            Notification::make()->title('Gagal Menandai Selesai')->body('Terjadi kesalahan: ' . $e->getMessage())->danger()->send();
                        }
                    })
                    
                    ->visible(fn (Model $record): bool =>
                        Auth::user()->is_admin && 
                        $record->pengajuan !== null && 
                        $record->pengajuan->status === 'menunggu_ttd'
                    ),
            ])
            ->bulkActions([ ])
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
            'index' => Pages\ListSuratKeluars::route('/'),
            'create' => Templates\ListTemplates::route('/create'),
            'view' => Pages\ViewSuratKeluar::route('/view/{record}'),
            // 'edit' => Pages\EditSuratKeluar::route('/{record}/edit'),
        ];
    }
}
