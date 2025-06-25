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

    public ?array $initialFormData = []; // Menyimpan data awal saat mount
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
                // Tambahkan field lain yang ingin ditampilkan dan diubah
                TextInput::make('nim')
                    ->label('NIM')
                    ->placeholder('00/000000/SV/00000')
                    ->mask('99/999999/aa/99999')
                    ->regex('/^\d{2}\/\d{6}\/[A-Z]{2}\/\d{5}?$/')
                    ->helperText(new HtmlString('Gunakan <b>huruf kapital</b> untuk kode <b>Fakultas</b>'))
                    ->required()
                    ->disabled(! $this->isEditing),
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
        // $this->form->fill($this->formData); // Isi form dengan data saat ini
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
            
            // Dispatch event untuk reload page (jika diperlukan)
            $this->dispatch('reload-page'); 

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Jika validasi gagal, Filament akan otomatis menampilkan pesan error di UI
            // Kamu bisa tambahkan log di sini jika perlu
            \Log::error('Validasi profil gagal:', ['errors' => $e->errors()]);
            Notification::make()
                ->danger()
                ->title('Gagal menyimpan profil!')
                ->body('Terdapat kesalahan pada input Anda. Silakan periksa kembali.')
                ->send();
        } catch (\Throwable $e) {
            // Tangani error lain yang mungkin terjadi saat menyimpan
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