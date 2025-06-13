<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Forms\Components\NimInput;
use App\Forms\Components\IpkInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EditTemplate extends EditRecord
{
    protected static string $resource = TemplateResource::class;

    protected ?string $heading = "Buat Surat Keluar";

    protected ?string $subheading = "Isi form berikut dan hasilkan surat keluar dengan mudah.";

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Bagian Baru: Isi Contoh Data Template
                Section::make('Data Surat ' . $this->record->name)
                    // ->description('Gunakan bagian ini untuk mengisi contoh data sesuai definisi template. Data ini dapat digunakan untuk pratinjau atau default.')
                    ->schema(function (\App\Models\Template $record): array {
                        $fields = [];
                        foreach ($record->form_schema as $fieldConfig) {
                            $fieldName = 'data_surat.' . $fieldConfig['name']; // Data akan disimpan ke `sample_data`

                            $filamentComponent = null;

                            // --- Panggil Custom Component berdasarkan 'name' fieldConfig ---
                            if ($fieldConfig['name'] === 'nim') {
                                $filamentComponent = NimInput::make($fieldName)
                                ->validationAttribute('NIM')
                                ->format();
                            } elseif ($fieldConfig['name'] === 'ipk') {
                                $filamentComponent = IpkInput::make($fieldName)
                                ->validationAttribute('IPK')
                                ->format();
                            } elseif ($fieldConfig['name'] === 'thn_akademik') {
                                $filamentComponent = TextInput::make($fieldName)
                                ->mask('9999/9999');
                            } elseif ($fieldConfig['name'] === 'nip') {
                                $filamentComponent = TextInput::make($fieldName)
                                ->minLength(18)
                                ->mask('999999999999999999');
                            } else {
                                // Panggil component Filament bawaan berdasarkan 'type'
                                switch ($fieldConfig['type']) {
                                    case 'textarea':
                                        $filamentComponent = Textarea::make($fieldName); // Pastikan Textarea diimport
                                        break;
                                    case 'number':
                                        $filamentComponent = TextInput::make($fieldName)->numeric();
                                        break;
                                    case 'date':
                                        $filamentComponent = DatePicker::make($fieldName); // Pastikan DatePicker diimport
                                        break;
                                    case 'select':
                                        $options = collect($fieldConfig['options'] ?? [])->mapWithKeys(function ($option) {
                                            return [$option['value'] => $option['label']];
                                        })->toArray();
                                        $filamentComponent = Select::make($fieldName)
                                            ->options($options);
                                        break;
                                    case 'text':
                                    default:
                                        $filamentComponent = TextInput::make($fieldName);
                                        break;
                                }
                            }

                            if ($filamentComponent) {
                                $filamentComponent
                                    ->label($fieldConfig['label'])
                                    // Default nilai dari `sample_data` record yang sedang diedit
                                    ->default(fn (Get $get) => $get($fieldName))
                                    ->helperText($fieldConfig['helper_text'] ?? null); // Gunakan helper_text dari definisi template

                                $fields[] = $filamentComponent;
                            }
                        }
                        return $fields;
                    })
                    ->columns(2), // Layout untuk field dinamis contoh data
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('generateSuratKeluar')
                ->label('Generate Surat Keluar')
                ->icon('heroicon-s-document-arrow-up')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generate Surat Keluar?')
                ->modalDescription('Ini akan membuat dokumen surat keluar, pastikan data yang Anda masukkan sudah sesuai.')
                ->modalSubmitActionLabel('Konfirmasi Generate')
                ->action(function () { // <-- Mengubah parameter dari Template $record menjadi kosong
                    // Akses $this->record untuk model Template yang sedang diedit
                    $templateRecord = $this->record;
                    // Akses data form dari $this->data
                    $formData = $this->data;

                    try {
                        // Data yang diisi di form dinamis berada di $formData['data_surat']
                        $dataSuratFromForm = $formData['data_surat'] ?? [];

                        // Asumsi Admin yang login adalah pembuat surat. Ambil data user admin.
                        // Jika tidak ada user_id yang terkait, Anda mungkin perlu membuat user dummy atau mengambil dari Auth::user()
                        $adminUser = auth()->user();
                        if (!$adminUser) {
                             Notification::make()
                                ->title('Error')
                                ->body('Pengguna tidak terautentikasi.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // $userMajorCode = $adminUser->major['kode'] ?? 'N/A'; // Ambil prodi dari admin yang login

                        // Prioritaskan nomor surat dari data form
                        $nomorSurat = 'NO.'. ($dataSuratFromForm['nomor_surat'] ?? 'AUTO')  . '/UN1/SV2-TEDI/AKM/PJ/'. date("Y") ;
                        $prodiSurat = $dataSuratFromForm['prodi']; // Prodi dari form atau dari user admin

                        $pdfPath = null;
                        try {
                            $viewData = [];
                            // Tambahkan semua isi $dataSuratFromForm langsung ke $viewData
                            foreach ($dataSuratFromForm as $key => $value) {
                                $viewData[$key] = $value;
                            }
                            // Tambahkan data dari record Template itu sendiri
                            $viewData['template'] = $templateRecord;
                            $viewData['user'] = $adminUser; 


                            $pdfContent = view('templates.' . $templateRecord->class_name, $viewData)->render();

                            $pdfFileName = 'surat_keluar_' . Str::slug($templateRecord->name) . '_' . Str::slug($dataSuratFromForm['nama'] ?? 'unknown') . '_' . time() . '.pdf';
                            Storage::disk('public')->put('surat_keluar/' . $pdfFileName, Pdf::loadHTML($pdfContent)->output());

                        } catch (\Exception $e) {
                             Notification::make()
                                ->title('Gagal Membuat PDF')
                                ->body('Terjadi kesalahan saat merender PDF: ' . $e->getMessage())
                                ->danger()
                                ->send();
                            return;
                        }

                        // Buat record SuratKeluar baru
                        $suratKeluar = \App\Models\SuratKeluar::create([
                            'nomor_surat' => $nomorSurat,
                            'prodi' => $prodiSurat,
                            'pdf_url' => $pdfFileName, // Simpan hanya nama file
                            'template_id' => $templateRecord->_id,
                            'pengajuan_id' => null, // Tidak ada pengajuan yang terkait langsung dari sini
                            'metadata' => $dataSuratFromForm // Simpan semua data input sebagai metadata
                        ]);

                        Notification::make()
                            ->title('Surat Keluar Berhasil Dibuat')
                            ->body('Nomor Surat: ' . $nomorSurat . ' telah dibuat. <a href="http://127.0.0.1:8000/storage/surat_keluar/'.$pdfFileName. '" target="_blank" class="underline">Lihat PDF</a>')
                            ->success()
                            ->send();

                        // Redirect ke halaman daftar template
                        return redirect()->route('filament.admin.resources.templates.index');

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Membuat Surat Keluar')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
                Actions\Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
