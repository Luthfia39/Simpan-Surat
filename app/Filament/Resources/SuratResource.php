<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratResource\Pages;
use App\Filament\Resources\SuratResource\RelationManagers;
use App\Models\Surat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Table;

class SuratResource extends Resource
{
    protected static ?string $model = Surat::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'List Surat';

    protected static ?string $slug = '/';

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
            // ->columns([
            //     Stack::make([
            //         IconColumn::make('name')
            //             ->icon('heroicon-s-document'),
            //         TextColumn::make('name')
            //             ->label('Nama')
            //             ->size(TextColumn\TextColumnSize::Medium)
            //             ->searchable(true),
            //     ])->space(2),
            // ])
            // ->contentGrid([
            //     'sm' => 1,
            //     'md' => 2,
            //     'xl' => 3,
            // ])
            // ->defaultSort('name')
            // ->recordUrl(
            //     fn (Model $record): string => Pages\CreateSuratFromTemplate::getUrl([$record->id]),
            // )
            ;
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
            'index' => Pages\ListSurats::route('/'),
            'create' => Pages\CreateSurat::route('/create'),
            'edit' => Pages\EditSurat::route('/{record}/edit'),
        ];
    }
}
