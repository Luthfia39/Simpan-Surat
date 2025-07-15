<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;

use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Forms\Components\NimInput;
use App\Enums\Major;
use Illuminate\Support\HtmlString;

class Profile extends Page implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament.pages.profile';

    protected ?string $heading = 'Profil Pengguna';

    protected static ?int $navigationSort = 2;

    public bool $isEditing = false;

    public ?array $initialFormData = []; 
    public ?array $formData = [];

    public function mount(): void
    {
        $this->initialFormData = Auth::user()->toArray();
        $this->formData = Auth::user()->toArray();
        $this->form->fill($this->formData);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->disabled(! $this->isEditing),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->disabled(true), 
                TextInput::make('nim')
                    ->label('NIM')
                    ->placeholder('00/000000/SV/00000')
                    ->mask('99/999999/aa/99999') 
                    ->helperText(new HtmlString('Gunakan <b>huruf kapital</b> untuk kode <b>Fakultas</b>'))
                    ->required() 
                    ->disabled(! $this->isEditing)
                    ->rules([ 
                        'required',
                        'string',
                        'regex:/^\d{2}\/\d{6}\/[A-Z]{2}\/\d{5}$/', 
                    ]),
                Select::make('prodi')
                    ->label('Program Studi')
                    ->options(Major::toArray())
                    ->required()
                    ->disabled(! $this->isEditing),
            ])
            ->statePath('formData')
            ->columns(2);
    }

    public function editProfile(): void
    {
        $this->isEditing = true;
    }

    public function save(): void
    {
        try {
            $this->form->validate(); 
            $this->isEditing = false;

            $user = Auth::user();
            $user->name = $this->formData['name'];
            $user->nim = $this->formData['nim'];
            $user->prodi = $this->formData['prodi'];
            $user->save();
            
            Notification::make()
                ->success()
                ->title('Profil berhasil diperbarui!')
                ->send();
            
            $this->dispatch('reload-page'); 
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->errors();
            $errorMessageDetail = '<ul>';
            foreach ($errorMessages as $field => $messages) { 
                if (is_array($messages)) {
                    foreach ($messages as $message) {
                        $errorMessageDetail .= '<li>' . e($message) . '</li>';
                    }
                } else {
                    $errorMessageDetail .= '<li>' . ': ' . e($messages) . '</li>';
                }
            }
            $errorMessageDetail .= '</ul>';

            Notification::make()
                ->title('Validasi Gagal!')
                ->body(new HtmlString('Ada beberapa kolom yang harus diisi atau tidak valid:<br>' . $errorMessageDetail))
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            \Log::error('Error menyimpan profil:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Notification::make()
                ->danger()
                ->title('Terjadi kesalahan!')
                ->body('Gagal memperbarui profil: ' . $e->getMessage())
                ->send();
        }
    }

    public function cancelEdit(): void
    {
        $this->isEditing = false;
        $this->dispatch('reload-page');
    }
}