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

class SuratKeluarResource extends Resource
{
    protected static ?string $model = SuratKeluar::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Surat Keluar';

    // protected static ?string $navigationGroup = 'Surat Keluar';

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

                TextColumn::make('major')
                    ->label('Program Studi')
                    // ->getStateUsing(fn (Model $record): ?string => 
                    //     $record->pdf_url ?? null
                    // )
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'TRPL' => 'warning',
                        'TRI' => 'info',
                        'TRE' => 'success',
                        'TRIK' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('major')
                    ->label('Program Studi')
                    ->options([
                        'TRPL' => 'Teknologi Rekayasa Perangkat Lunak',
                        'TRI' => 'Teknologi Rekayasa Internet',
                        'TRE' => 'Teknologi Rekayasa Elektro',
                        'TRIK' => 'Teknologi Rekayasa Instrumentasi dan Kontrol',
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
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSuratKeluars::route('/'),
            'create' => Templates\ListTemplates::route('/create'),
            'view' => Pages\ViewSuratKeluar::route('/view/{record}'),
            // 'edit' => Pages\EditSuratKeluar::route('/{record}/edit'),
        ];
    }
}
