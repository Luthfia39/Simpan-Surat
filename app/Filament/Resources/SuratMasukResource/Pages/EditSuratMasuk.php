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
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Filament\Actions\Action;
use Livewire\Attributes\On;

class EditSuratMasuk extends Page
{
    protected static string $resource = SuratMasukResource::class;

    protected static string $view = 'filament.resources.surat-masuk-resource.pages.edit';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public ?string $taskId = '';
    public ?object $record = null;

    public $annotations;
    public string $ocr = '';

    // protected $listeners = ['updateAnnotations' => 'updateAnnotations'];

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    use InteractsWithFormActions;

    public function mount(): void
    {
        $this->taskId = request()->route('taskId')?? Session::get('current_task_id');

        $ocrData = Surat::where('task_id', (string) $this->taskId)->first();

        Log::info('Surat dari yang mau diedit:', ['surat' => $ocrData, 'taskId' => $this->taskId]);
        $this->ocr = $ocrData['ocr_text'] ?? '';

        $this->dispatch('ocr-loaded', [
            'ocr' => $ocrData['ocr_text'],
            'extracted_fields' => $ocrData['extracted_fields'],
        ]);

        // dd($this->ocr);

        $this->form->fill([
            'pdf_path' => $ocrData->pdf_url,
            'ocr_text' => $this->ocr,
            'letter_type' => $ocrData['letter_type'],
            // 'extracted_fields' => $ocrData['extracted_fields'] ?? '',
            // 'pengirim' => $ocrData['extracted_fields']['pengirim'] ?? '',
            // 'penandatangan' => $ocrData['extracted_fields']['penandatangan'] ?? '',
        ]);
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Hidden::make('pdf_path'),
                \Filament\Forms\Components\Hidden::make('ocr_text'),
                // \Filament\Forms\Components\Hidden::make('extracted_fields')->default([]),
                \Filament\Forms\Components\Select::make('letter_type')->label('Jenis Surat')->options([
                    'Surat Pernyataan' => 'Surat Pernyataan',
                    'Surat Keterangan' => 'Surat Keterangan',
                    'Surat Tugas' => 'Surat Tugas',
                ]),
                // \Filament\Forms\Components\TextInput::make('nomor_surat')->label('Nomor Surat'),
                // \Filament\Forms\Components\TextInput::make('pengirim')->label('Pengirim'),
                // \Filament\Forms\Components\TextInput::make('penandatangan')->label('Penanda Tangan'),
            ])
            ->statePath('data');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->action(function () {
                    $this->onProcessSave();
                }),
        ];
    }

    #[On('data-ready')]
    public function updateData($ocr_final, $annotations)
    {
        $this->ocr= $ocr_final;
        $this->annotations= $annotations;
        $this->save();
    }

    protected function onProcessSave() {
        $this->dispatch('update-data');
    }

    protected function save()
    {
        $data = $this->form->getState();

        $grouped = [];

        // $decoded = json_decode($this->annotations, true);
        // $extracted_fields = is_array($decoded) ? $decoded : [];

        foreach ($this->annotations as $annotation) {
            $type = key($annotation);
            $text = $annotation[$type];

            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }

            $grouped[$type][] = $text;
        }

        // // Simpan ke MongoDB
        $surat = Surat::where('task_id', $this->taskId)->firstOrNew();

        $surat->fill([
            // 'task_id' => $this->taskId,
            'ocr_text' => $this->ocr,
            'letter_type' => $data['letter_type'],
            'extracted_fields' => $grouped,
            // 'extracted_fields' => json_encode($grouped),
            'pdf_url' => $data['pdf_path'],
        ]);

        $surat->save();

        Notification::make()
            ->title('Surat berhasil disimpan')
            ->success()
            ->send();

        return redirect()->to(route('filament.admin.resources...index'));
    }
}
