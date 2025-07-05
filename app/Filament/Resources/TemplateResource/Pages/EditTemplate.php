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
use Illuminate\Validation\ValidationException;

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
                    ->schema(function (\App\Models\Template $record): array {

                        $fields = [];
                        foreach ($record->form_schema as $fieldConfig) {
                            $fieldName = 'data_surat.' . $fieldConfig['name']; 

                            $filamentComponent = null;

                            if ($fieldConfig['name'] === 'nim' && class_exists(NimInput::class)) { 
                                $filamentComponent = NimInput::make($fieldName)
                                    ->validationAttribute('NIM')
                                    ->format();
                            } elseif ($fieldConfig['name'] === 'ipk' && class_exists(IpkInput::class)) { 
                                $filamentComponent = IpkInput::make($fieldName)
                                    ->validationAttribute('IPK')
                                    ->format();
                            } elseif ($fieldConfig['name'] === 'thn_akademik') {
                                $filamentComponent = TextInput::make($fieldName)
                                    ->mask('9999/9999')
                                    ->rules([
                                        'required',
                                        'regex:/^\d{4}\/\d{4}$/', 
                                        function ($attribute, $value, $fail) {
                                            [$year1, $year2] = explode('/', $value);
                                
                                            if ((int)$year2 !== (int)$year1 + 1) {
                                                $fail("Tahun kedua harus merupakan tahun pertama ditambah 1. Contoh: 2024/2025");
                                            }
                                        },
                                    ]);
                            } elseif ($fieldConfig['name'] === 'nip') {
                                $filamentComponent = TextInput::make($fieldName)->minLength(18)->mask('999999999999999999');
                            }
                            
                            elseif ($fieldConfig['type'] === 'repeater') {
                                $subFieldsSchema = [];
                                foreach ($fieldConfig['sub_schema'] ?? [] as $subFieldConfig) {
                                    $subFieldName = $fieldName . '.' . $subFieldConfig['name']; 
                                    $subFilamentComponent = null;

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

                                    if ($subFieldConfig['name'] === 'nim') { 
                                        $subFilamentComponent = TextInput::make($subFieldName)
                                            ->placeholder('00/000000/SV/00000')
                                            ->mask('99/999999/aa/99999')
                                            ->regex('/^\d{2}\/\d{6}\/[A-Z]{2}\/\d{5}$/');
                                    } elseif ($subFieldConfig['name'] === 'ipk' && class_exists(IpkInput::class)) { 
                                        $subFilamentComponent = IpkInput::make($subFieldName)
                                            ->validationAttribute('IPK')
                                            ->format();
                                    } elseif ($subFieldConfig['name'] === 'thn_akademik') {
                                        $subFilamentComponent = TextInput::make($subFieldName)->mask('9999/9999');
                                    } elseif ($subFieldConfig['name'] === 'nip') {
                                        $subFilamentComponent = TextInput::make($subFieldName)->minLength(18)->mask('999999999999999999');
                                    } elseif ($subFieldConfig['name'] === 'pukul') {
                                        $subFilamentComponent = TextInput::make($subFieldName)->mask('99.99 s.d 99.99');
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
                                    ->addActionLabel($fieldConfig['add_action_label'] ?? 'Tambah Item') 
                                    ->reorderableWithButtons() 
                                    ->columns(2) 
                                    ->columnSpan('full') 
                                    ->maxItems($fieldConfig['max_items'] ?? null) 
                                    ->required($fieldConfig['required'] ?? false)
                                    ->default($fieldConfig['default'] ?? []); 
                            }
                            else {
                                switch ($fieldConfig['type']) {
                                    case 'textarea':
                                        $filamentComponent = Textarea::make($fieldName);
                                        break;
                                    case 'number':
                                        $filamentComponent = TextInput::make($fieldName)->numeric()->minValue(1);
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
                                    ->required($fieldConfig['required'] ?? false); 

                                $fields[] = $filamentComponent;
                            }
                        }
                        return $fields;
                    })
                    ->columns(2), 
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
                ->action(function () {
                    try {
                        $this->form->validate(); 

                        $templateRecord = $this->record;
                        $formData = $this->data; 

                        $dataSuratFromForm = $formData['data_surat'] ?? [];

                        $adminUser = auth()->user();
                        if (!$adminUser) {
                             Notification::make()
                                ->title('Error')
                                ->body('Pengguna tidak terautentikasi.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $nomorSurat = $templateRecord->class_name === 'keterangan-aktif-kuliah' || $templateRecord->class_name === 'rekomendasi-beasiswa' ? 'NO.'. ($dataSuratFromForm['nomor_surat'] ?? 'AUTO')  . '/UN1/SV.2-TEDI/KM/'. date("Y") : 'NO.'. ($dataSuratFromForm['nomor_surat'] ?? 'AUTO')  . '/UN1/SV.2-TEDI/AKM/PJ/'. date("Y");
                        $prodiSurat = $dataSuratFromForm['prodi'] ?? null; 

                        $pdfPath = null;
                        try {
                            $viewData = [];
                            foreach ($dataSuratFromForm as $key => $value) {
                                $viewData[$key] = $value;
                            }
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

                        $suratKeluar = \App\Models\SuratKeluar::create([
                            'nomor_surat' => $nomorSurat,
                            'prodi' => $prodiSurat,
                            'pdf_url' => $pdfFileName, 
                            'template_id' => $templateRecord->_id,
                            'pengajuan_id' => null, 
                            'metadata' => $dataSuratFromForm,
                            'is_show' => false
                        ]);

                        Notification::make()
                            ->title('Surat Keluar Berhasil Dibuat')
                            ->body('Nomor Surat: ' . $nomorSurat . ' telah dibuat. <a href="' . asset('surat_keluar/' . $pdfFileName) . '" target="_blank" class="underline">Lihat Draf PDF</a>')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));

                    } catch (ValidationException $e) { 
                        $errorMessages = $e->errors();

                        // Gabungkan pesan-pesan error menjadi satu string atau daftar
                        $errorMessageDetail = '<ul>';
                        foreach ($errorMessages as $field => $messages) { 
                            if (is_array($messages)) {
                                foreach ($messages as $message) {
                                    // Tambahkan nama field ke pesan jika perlu untuk konteks lebih baik
                                    $displayField = str_replace(['data_surat.', '_'], ['',' '], $field); 
                                    $errorMessageDetail .= '<li>' . e($message) . '</li>';
                                }
                            } else {
                                // Fallback jika ada pesan yang langsung string (jarang)
                                $displayField = str_replace(['data_surat.', '_'], ['',' '], $field);
                                $errorMessageDetail .= '<li>' . ': ' . e($messages) . '</li>';
                            }
                        }
                        $errorMessageDetail .= '</ul>';

                        // Kirim notifikasi dengan detail error
                        Notification::make()
                            ->title('Validasi Gagal!')
                            ->body(new HtmlString('Ada beberapa kolom yang harus diisi atau tidak valid:<br>' . $errorMessageDetail)) 
                            ->danger()
                            ->send();
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
