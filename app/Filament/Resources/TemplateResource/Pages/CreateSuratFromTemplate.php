<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Js;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\SuratKeluar;
use Throwable;

class CreateSuratFromTemplate extends Page
{
    use InteractsWithFormActions;
    use InteractsWithRecord;
    
    protected static string $resource = TemplateResource::class;

    protected static string $view = 'filament.resources.template-resource.pages.create-surat-from-template';

    public ?array $data = [];

    public string $class;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->getClassFile();

        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill();

        $this->callHook('afterFill');
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();

        return $record->name;
    }

    private function getClassFile(): void
    {
        $namespace = 'App\\Filament\\Resources\\TemplateResource\\Templates\\';
        $class = $namespace.$this->record->class_name;

        $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $path = lcfirst($path);
        
        if (file_exists(base_path($path).'.php')) {
            $this->class = $class;
        } else {
            abort(404);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->class::getSchema())
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('Create')
            ->label('Buat')
            ->action(function () {
                return $this->generatePdf();
            });
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Batal')
            ->alpineClickHandler('document.referrer ? window.history.back() : (window.location.href = '.Js::from($this->previousUrl ?? static::getResource()::getUrl()).')')
            ->color('gray');
    }

    // protected function generatePdf(): StreamedResponse|false
    // {
    //     $data = $this->form->getState();

    //     try {
    //         $view = $this->class::$view;
    //         $pdf = Pdf::loadView($view, $data);

    //         Notification::make()
    //             ->success()
    //             ->title('Surat berhasil dibuat')
    //             ->send();

    //         $filename = $this->getRecord()->name.'.pdf';

    //         // Simpan file ke storage
    //         $filePath = $data['file_path']->store('suratKeluar', 'public');

    //         return response()->streamDownload(function () use ($pdf) {
    //             echo $pdf->stream();
    //         }, $filename);
    //     } catch (Throwable $exception) {
    //         Notification::make()
    //             ->danger()
    //             ->title($exception)
    //             ->send();

    //         return false;
    //     }
    // }

    protected function generatePdf(): StreamedResponse|false
    {
        $data = $this->form->getState();

        try {
            // 1. Load view template surat
            $view = $this->class::$view; // Contoh: 'exports.surat-template'
            $pdf = Pdf::loadView($view, $data);

            // 2. Buat nama file unik
            $filename = $this->getRecord()->name . '-' . now()->format('Y-m-d_H-i-s') . '.pdf';
            $storagePath = 'suratKeluar/' . $filename;

            // 3. Simpan PDF ke storage (public disk)
            Storage::disk('public')->put($storagePath, $pdf->output());

            SuratKeluar::create([
                // 'name' => $this->getRecord()->name,
                'pdf_url' => $filename,
                'major' => $data['prodi'],
            ]);

            // 5. Tampilkan notifikasi sukses
            Notification::make()
                ->success()
                ->title('Surat berhasil dibuat')
                ->send();

            // 6. Download file sebagai stream
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, $filename);

        } catch (Throwable $exception) {
            Notification::make()
                ->danger()
                ->title('Gagal membuat surat')
                ->body($exception->getMessage())
                ->send();

            return false;
        }
    }
}
