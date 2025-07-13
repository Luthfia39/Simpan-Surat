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
use App\Models\Template;

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
                    ->label('Jenis Surat')
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
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $searchLower = trim(mb_strtolower($search, 'UTF-8'));
                        $allMajors = Major::toArray();
                        $matchingProdiCodes = []; 
                        
                        foreach ($allMajors as $code => $name) {
                            $nameLower = mb_strtolower($name, 'UTF-8');
                            
                            if (str_contains($nameLower, $searchLower)) {
                                $matchingProdiCodes[] = $code; 
                            }
                        }

                        foreach ($allMajors as $code => $name) {
                            $codeLower = mb_strtolower($code, 'UTF-8');
                            if (str_contains($codeLower, $searchLower) && !in_array($code, $matchingProdiCodes)) {
                                $matchingProdiCodes[] = $code;
                            }
                        }
                        
                        if (!empty($matchingProdiCodes)) {
                            $regexValues = implode('|', array_map(fn($code) => preg_quote($code, '/'), $matchingProdiCodes));
                            $regexPattern = '/(?i).*"prodi":"(' . $regexValues . ')".*/';
                            $query->where('metadata', 'regex', $regexPattern);
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                        
                        return $query;
                    }),
                TextColumn::make('created_at')
                    ->label('Waktu Pembuatan')
                    ->getStateUsing(function ($record): ?string {
                        if ($record->created_at instanceof \DateTimeInterface) {
                            return Carbon::parse($record->created_at)->locale('id')->translatedFormat('l, j F Y');
                        }; 
                    })
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $searchLower = trim(mb_strtolower($search, 'UTF-8'));

                        $query->where(function (Builder $q) use ($searchLower) {
                            try {
                                $parsedDate = Carbon::parse($searchLower, 'id');
                                $q->orWhereDate('created_at', $parsedDate->toDateString());
                            } catch (\Exception $e) { }

                            if (preg_match('/^\d{4}$/', $searchLower)) {
                                $q->orWhereYear('created_at', (int)$searchLower);
                            }

                            $monthMap = [
                                'januari' => '01', 'jan' => '01',
                                'februari' => '02', 'feb' => '02',
                                'maret' => '03', 'mar' => '03',
                                'april' => '04', 'apr' => '04',
                                'mei' => '05',
                                'juni' => '06', 'jun' => '06',
                                'juli' => '07', 'jul' => '07',
                                'agustus' => '08', 'agu' => '08',
                                'september' => '09', 'sep' => '09',
                                'oktober' => '10', 'okt' => '10',
                                'november' => '11', 'nov' => '11',
                                'desember' => '12', 'des' => '12',
                            ];
                            $monthNumber = null;
                            if (preg_match('/^\d{1,2}$/', $searchLower) && (int)$searchLower >= 1 && (int)$searchLower <= 12) {
                                $monthNumber = (int)$searchLower; 
                            } elseif (isset($monthMap[$searchLower])) {
                                $monthNumber = (int)$monthMap[$searchLower]; 
                            }

                            if ($monthNumber) {
                                $q->orWhereMonth('created_at', $monthNumber);
                            }

                            if (preg_match('/^\d{1,2}$/', $searchLower) && (int)$searchLower >= 1 && (int)$searchLower <= 31) {
                                $dayNumber = (int)$searchLower; 
                                $q->orWhereDay('created_at', $dayNumber);
                            }
                            
                            $q->orWhere('created_at', 'like', '%' . $searchLower . '%');
                        });

                        return $query;
                    }),
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
                    Tables\Filters\SelectFilter::make('jenis_surat')
                    ->label('Jenis Surat')
                    ->options(function () {
                        $templateOptions = Template::all()->where('for_user', true)->pluck('name', '_id')->toArray();
                    
                        return array_merge(
                            ['' => 'Semua Jenis Surat'],
                            $templateOptions
                        );
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value']) && !empty($data['value'])) {
                            $filterValue = trim($data['value']);
                            $query->where('template_id', $data['value']);
                        }
                        return $query;
                    })
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

                            $pengajuanTerkait = $record->pengajuan;
                            if ($pengajuanTerkait) {
                                $pengajuanTerkait->status = 'selesai';
                                $pengajuanTerkait->keterangan = $data['keterangan_final'];
                                $pengajuanTerkait->save();
                                Notification::make()->title('Pengajuan Berhasil Ditandai Selesai')
                                ->body('Surat final berhasil diunggah/dicatat dan pengajuan telah ditandai selesai.')
                                ->success()->send();
                            } else {
                                Notification::make()->title('Peringatan')
                                ->body('Surat final berhasil diunggah/dicatat, tetapi tidak ada pengajuan terkait yang ditemukan untuk diupdate statusnya.')
                                ->warning()->send();
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
