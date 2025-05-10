<?php

namespace App\Filament\Resources\SuratMasukResource\Pages;

use App\Filament\Resources\SuratMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Page;
use App\Models\Surat;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\URL;

class EditSuratMasuk extends Page
{
    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.edit';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    use InteractsWithForms;

    public function mount(): void
    {
        // Data awal kosong
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // TextEntry::make('ocr-content')
                //     ->tooltip('Isi surat akan muncul disini')
                //     ->default('Nomor\nLamp\nHal\n\nKepada :\n\nUNIVERSITAS GADJAH MADA\nSEKOLAH VOKASI ee'),
                Wizard::make([
                    Step::make('Jenis Surat')
                        ->schema([
                            Select::make('jenis_surat')
                                ->options([
                                    'resmi' => 'Surat Resmi',
                                    'undangan' => 'Surat Undangan',
                                    'permohonan' => 'Surat Permohonan',
                                ])
                                ->required(),
                        ]),

                    Step::make('Nomor Surat')
                        ->schema([
                            TextInput::make('nomor_surat')
                                ->label('Nomor Surat')
                                ->extraInputAttributes(['id' => 'nomor_surat', 'class' => 'highlight-target']),
                        ]),

                    Step::make('Isi Ringkas')
                        ->schema([
                            TextInput::make('isi_ringkas')
                                ->label('Isi Ringkas Surat')
                                ->extraInputAttributes(['id' => 'isi_ringkas', 'class' => 'highlight-target']),
                        ]),

                    Step::make('Penanda Tangan')
                        ->schema([
                            TextInput::make('penandatangan')
                                ->label('Penanda Tangan')
                                ->extraInputAttributes(['id' => 'penandatangan', 'class' => 'highlight-target']),
                        ]),

                    Step::make('Tanggal Diterima')
                        ->schema([
                            DatePicker::make('tanggal_diterima')
                                ->label('Tanggal Surat Diterima')
                                ->extraInputAttributes(['id' => 'tanggal_diterima', 'class' => 'highlight-target']),
                        ]),

                    Step::make('Pengirim Surat')
                        ->schema([
                            TextInput::make('pengirim')
                                ->label('Nama Pengirim')
                                ->extraInputAttributes(['id' => 'pengirim', 'class' => 'highlight-target']),
                        ]),
                ])
                ->submitAction('submit')
                // ->cancelAction(URL::previous()),
            ])
            ->statePath('data');
    }

    protected function goBack() {
        back();
    }

    public function submit()
    {
        // Simpan ke database atau proses lebih lanjut
        dd($this->form->getState());
    }
}
