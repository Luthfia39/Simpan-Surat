<?php

namespace App\Filament\Resources\SuratResource\Pages;

use App\Filament\Resources\SuratResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class ListSurats extends ListRecords
{
    protected static string $resource = SuratResource::class;

    public function table(Table $table): Table
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}