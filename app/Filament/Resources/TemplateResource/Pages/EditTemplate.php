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
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Repeater;

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
                // Bagian ini akan menampilkan input-input dinamis
                Section::make('Data Surat ' . ($this->record->name ? $this->record->name :''))
                    ->description('Silakan isi data yang dibutuhkan untuk surat ini.')
                    // Fungsi ini akan dieksekusi untuk membangun schema form
                    // $record di sini adalah instance model dari record yang sedang dibuat/diedit (misal model Surat atau PermintaanSurat)
                    ->schema(function (\App\Models\Template $record): array {
                        // Ambil `template_id` yang dipilih oleh pengguna (jika ada dropdown template di form ini)
                        // Atau, jika template sudah fix, ambil langsung dari DB

                        $fields = [];
                        // Iterasi melalui skema form yang didefinisikan di template
                        foreach ($record->form_schema as $fieldConfig) {
                            // Nama field di database (misal: data_surat.nama_lengkap)
                            // Ini akan menjadi path untuk menyimpan data input pengguna
                            $fieldName = 'data_surat.' . $fieldConfig['name']; 

                            $filamentComponent = null;

                            // --- PENANGANAN TIPE INPUT KHUSUS / CUSTOM COMPONENT ---
                            if ($fieldConfig['name'] === 'nim' && class_exists(NimInput::class)) { // Cek NimInput ada
                                $filamentComponent = NimInput::make($fieldName)
                                    ->validationAttribute('NIM')
                                    ->format();
                            } elseif ($fieldConfig['name'] === 'ipk' && class_exists(IpkInput::class)) { // Cek IpkInput ada
                                $filamentComponent = IpkInput::make($fieldName)
                                    ->validationAttribute('IPK')
                                    ->format();
                            } elseif ($fieldConfig['name'] === 'thn_akademik') {
                                $filamentComponent = TextInput::make($fieldName)->mask('9999/9999');
                            } elseif ($fieldConfig['name'] === 'nip') {
                                $filamentComponent = TextInput::make($fieldName)->minLength(18)->mask('999999999999999999');
                            }
                            
                            elseif ($fieldConfig['type'] === 'repeater') {
                                // Jika tipe adalah 'repeater', kita akan membuat Repeater baru
                                $subFieldsSchema = [];
                                // Iterasi melalui `sub_schema` yang didefinisikan di Template
                                foreach ($fieldConfig['sub_schema'] ?? [] as $subFieldConfig) {
                                    $subFieldName = $fieldName . '.' . $subFieldConfig['name']; // Path untuk sub-field (misal: data_surat.kelompok.0.nama)
                                    $subFilamentComponent = null;

                                    // Penanganan tipe input untuk sub-field
                                    switch ($subFieldConfig['type']) {
                                        case 'textarea':
                                            $subFilamentComponent = Textarea::make($subFieldName);
                                            break;
                                        case 'number':
                                            $subFilamentComponent = TextInput::make($subFieldName)->numeric();
                                            break;
                                        case 'date':
                                            $subFilamentComponent = DatePicker::make($subFieldName);
                                            break;
                                        case 'select':
                                            // Handle select options for sub-field if needed
                                            $subOptions = collect($subFieldConfig['options'] ?? [])->mapWithKeys(function ($option) {
                                                return [$option['value'] => $option['label']];
                                            })->toArray();
                                            $subFilamentComponent = Select::make($subFieldName)->options($subOptions);
                                            break;
                                        case 'text':
                                        default:
                                            $subFilamentComponent = TextInput::make($subFieldName);
                                            break;
                                    }

                                    if ($subFieldConfig['name'] === 'nim') { // Cek NimInput ada
                                        $subFilamentComponent = TextInput::make($subFieldName)
                                            ->placeholder('00/000000/SV/00000')
                                            ->mask('99/999999/aa/99999')
                                            ->regex('/^\d{2}\/\d{6}\/[A-Z]{2}\/\d{5}$/');
                                            // dd($subFilamentComponent);
                                    } elseif ($subFieldConfig['name'] === 'ipk' && class_exists(IpkInput::class)) { // Cek IpkInput ada
                                        $subFilamentComponent = IpkInput::make($subFieldName)
                                            ->validationAttribute('IPK')
                                            ->format();
                                    } elseif ($subFieldConfig['name'] === 'thn_akademik') {
                                        $subFilamentComponent = TextInput::make($subFieldName)->mask('9999/9999');
                                    } elseif ($subFieldConfig['name'] === 'nip') {
                                        $subFilamentComponent = TextInput::make($subFieldName)->minLength(18)->mask('999999999999999999');
                                    } 

                                    if ($subFilamentComponent) {
                                        $subFilamentComponent
                                            ->label($subFieldConfig['label'])
                                            ->default($subFieldConfig['default'] ?? null)
                                            ->helperText(new HtmlString($subFieldConfig['helper_text'] ?? null))
                                            ->required($subFieldConfig['required'] ?? false);
                                            
                                        $subFieldsSchema[] = $subFilamentComponent;
                                    }
                                }

                                $filamentComponent = Repeater::make($fieldName)
                                    ->label($fieldConfig['label'])
                                    ->schema($subFieldsSchema)
                                    ->addActionLabel($fieldConfig['add_action_label'] ?? 'Tambah Item') // Label untuk tombol tambah repeater
                                    ->reorderableWithButtons() // Agar bisa diurutkan ulang
                                    ->columns(2) // Jumlah kolom dalam repeaters
                                    ->columnSpan('full') // Agar repeater memenuhi lebar
                                    ->maxItems($fieldConfig['max_items'] ?? null) // Batasan jumlah item
                                    ->required($fieldConfig['required'] ?? false)
                                    ->default($fieldConfig['default'] ?? []); // Default untuk repeater adalah array kosong
                            }
                            // --- AKHIR PENANGANAN TIPE REPEATER ---
                            else {
                                // === PENANGANAN TIPE INPUT BAWAAN FILAMENT ===
                                switch ($fieldConfig['type']) {
                                    case 'textarea':
                                        $filamentComponent = Textarea::make($fieldName);
                                        break;
                                    case 'number':
                                        $filamentComponent = TextInput::make($fieldName)->numeric();
                                        break;
                                    case 'date':
                                        $filamentComponent = DatePicker::make($fieldName);
                                        break;
                                    case 'select':
                                        $options = collect($fieldConfig['options'] ?? [])->mapWithKeys(function ($option) {
                                            return [$option['value'] => $option['label']];
                                        })->toArray();
                                        $filamentComponent = Select::make($fieldName)->options($options);
                                        break;
                                    case 'text':
                                    default:
                                        $filamentComponent = TextInput::make($fieldName)->minLength(2);
                                        break;
                                }
                            }

                            if ($filamentComponent) {
                                $filamentComponent
                                    ->label($fieldConfig['label'])
                                    ->default($fieldConfig['default'] ?? null)
                                    ->helperText(new HtmlString($fieldConfig['helper_text'] ?? null))
                                    ->required($fieldConfig['required'] ?? false); // Ambil `required` dari template

                                $fields[] = $filamentComponent;
                            }
                        }
                        return $fields;
                    })
                    ->columns(2), // Layout untuk field dinamis contoh data
            ])
            // statePath akan menentukan di mana data input dinamis ini disimpan di model
            // Misalnya, jika model punya kolom 'data_surat' dengan cast 'array' atau 'json'
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
