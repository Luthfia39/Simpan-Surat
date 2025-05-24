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

class SuratMasukResource extends Resource
{
    protected static ?string $model = Surat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    // protected static ?string $navigationGroup = 'Surat Masuk';

    protected static ?string $navigationLabel = 'Surat Masuk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file_path')
                ->label('Unggah File Surat')
                ->acceptedFileTypes(['application/pdf'])
                ->required()
                ->storeFiles(false)
                ->maxFiles(1)
                ->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->getStateUsing(fn (Model $record): ?string => 
                        $record->extracted_fields['nomor_surat'][0] ?? null
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
                        $record->extracted_fields['penanda_tangan'][0] ?? null
                    )
                    ->default('-')
                    ->searchable(),

                TextColumn::make('pengirim')
                    ->label('Pengirim/Penerima')
                    ->getStateUsing(fn (Model $record): ?string => 
                        $record->extracted_fields['pengirim'][0] ?? null
                    )
                    ->default('-')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->getStateUsing(fn (Model $record): ?string => 
                        $record->extracted_fields['tanggal'][0] ?? null
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
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListSuratMasuks::route('/'),
            'create' => Pages\CreateSuratMasuk::route('/create'),
            'edit' => Pages\EditSuratMasuk::route('/edit/{record}'),
        ];
    }
}
