<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratMasukResource\Pages;
use App\Filament\Resources\SuratMasukResource\RelationManagers;
use App\Models\Surat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\TextInput;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Select;

use App\Filament\Pages\ReviewOCR;

use Illuminate\Support\Facades\Auth;

class SuratMasukResource extends Resource
{
    protected static ?string $model = Surat::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Surat Masuk';

    protected static ?string $navigationBadgeTooltip = 'Jumlah Surat Masuk yang Menunggu Review';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::query()->where('review_status', 'pending_review')->count() > 0 ? (string) static::getModel()::query()->where('review_status', 'pending_review')->count() : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('task_id'), 
                Forms\Components\Hidden::make('document_index'), 
                
                Select::make('letter_type')
                    ->label('Jenis Surat')
                    ->options([
                        'Surat Pernyataan' => 'Surat Pernyataan',
                        'Surat Keterangan' => 'Surat Keterangan',
                        'Surat Tugas' => 'Surat Tugas',
                        'Surat Rekomendasi Beasiswa' => 'Surat Rekomendasi Beasiswa',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('task_id')
                    ->label('Task ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('review_status')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->label('Status Review')
                    ->colors([
                        'gray' => 'pending_review',
                        'info' => 'in_review',
                        'success' => 'reviewed',
                        'danger' => 'rejected',
                    ])
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->getStateUsing(fn (Model $record): ?string => 
                        (is_string($record->extracted_fields) && ($decodedFields = json_decode($record->extracted_fields, true)) && is_array($decodedFields) && isset($decodedFields['nomor_surat']['text']))
                        ? $decodedFields['nomor_surat']['text']
                        : null
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('letter_type')
                    ->label('Jenis Surat')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Surat Permohonan' => 'warning',
                        'Surat Keterangan' => 'info',
                        'Surat Tugas' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('penanda_tangan')
                    ->label('Penanda Tangan')
                    ->getStateUsing(fn (Model $record): ?string => 
                        (is_string($record->extracted_fields) && ($decodedFields = json_decode($record->extracted_fields, true)) && is_array($decodedFields) && isset($decodedFields['ttd_surat']['text']))
                        ? $decodedFields['ttd_surat']['text']
                        : null
                    )
                    ->default('-')
                    ->searchable(),

                TextColumn::make('pengirim')
                    ->label('Pengirim/Penerima')
                    ->getStateUsing(fn (Model $record): ?string => 
                        (is_string($record->extracted_fields) && ($decodedFields = json_decode($record->extracted_fields, true)) && is_array($decodedFields) && isset($decodedFields['pengirim']['text']))
                        ? $decodedFields['pengirim']['text']
                        : null
                    )
                    ->default('-')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->getStateUsing(fn (Model $record): ?string => 
                        (is_string($record->extracted_fields) && ($decodedFields = json_decode($record->extracted_fields, true)) && is_array($decodedFields) && isset($decodedFields['tanggal']['text']))
                        ? $decodedFields['tanggal']['text']
                        : null
                    )
                    // ->date('d F Y') // atau ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('letter_type')
                    ->label('Jenis Surat')
                    ->options([
                        'Surat Pernyataan' => 'Surat Pernyataan',
                        'Surat Keterangan' => 'Surat Keterangan',
                        'Surat Tugas' => 'Surat Tugas',
                    ]),
                SelectFilter::make('review_status')
                    ->label('Status Review')
                    ->options([
                        'pending_review' => 'Belum Direview',
                        'in_review' => 'Sedang Direview',
                        'reviewed' => 'Sudah Direview',
                        'rejected' => 'Ditolak',
                    ])
                    // ->default('pending_review'),
            
                // Filter::make('created_at')
                //     ->form([
                //         \Filament\Forms\Components\DatePicker::make('created_from')->label('Tanggal Mulai'),
                //         \Filament\Forms\Components\DatePicker::make('created_until')->label('Tanggal Sampai'),
                //     ])
                //     ->query(fn (Builder $query, array $data) =>
                //         $query
                //             ->when($data['created_from'], fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                //             ->when($data['created_until'], fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']))
                //     ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(fn (Model $record): string => $record->review_status === 'reviewed' ? 'Lihat Hasil OCR' : 'Review OCR'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSuratMasuks::route('/'),
            'create' => Pages\CreateSuratMasuk::route('/create'),
            'edit' => Pages\EditSuratMasuks::route('/{record}/edit'),
            'view' => Pages\ViewSuratMasuk::route('/view/{record}'),
        ];
    }
}
