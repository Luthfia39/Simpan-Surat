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

use App\Enums\Major;

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
                        
                        return Major::getNameByCode($prodiCode) ?? $prodiCode ?? '-';
                    })
                    ->sortable()
                    // ->searchable(
                    //     isIndividual: true,
                    //     callback: fn (Builder $query, string $search) => 
                    //         $query->where('metadata.prodi', 'regex', new \MongoDB\BSON\Regex($search, 'i'))
                    // )
                    // ->searchable(
                    //     isIndividual: true,
                    //     callback: function (Builder $query, string $search) {
                    //         $query->where('metadata.prodi', 'regex', new \MongoDB\BSON\Regex($search, 'i'));
                    //     }
                    // ),
            ])
            ->filters([
                // SelectFilter::make('Prodi')
                //     ->options(Major::toArray())
                //     ->modifyQueryUsing(function (Builder $query, array $state)
                //         {
                //             if (!empty($state['value'])) {
                                // $a = json_decode(SuratKeluar::all()->toJson(), true);
                                // dd($a);
                                // dd(SuratKeluar::where('metadata.prodi', 'TRI')->get());
                            // }
                            // return $query->where('metadata.prodi', $state['value']);
                            // dd($state);
                            // return $state ? dd($state) : $query;
                            // return $state ? $query->where(['metadata.prodi'], $state['value']) : $query;
                    //     }
                    // ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
