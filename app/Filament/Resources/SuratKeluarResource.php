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
                        return '-'; // Default jika tidak ada data
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
