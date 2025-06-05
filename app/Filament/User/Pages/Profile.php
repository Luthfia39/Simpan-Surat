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

class Profile extends Page implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament.pages.profile';

    protected ?string $heading = 'Profil Pengguna';

    protected static ?int $navigationSort = 2;

    #[Url(history: true)]
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
                    ->disabled(true), // Contoh: email tidak bisa diubah
                // Tambahkan field lain yang ingin ditampilkan dan diubah
                NimInput::make('nim')
                    ->label('NIM')
                    ->validationAttribute('NIM')
                    ->format()
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
        $this->form->fill($this->formData); // Isi form dengan data saat ini
    }

    public function save(): void
    {
        $this->isEditing = false;
        // Mengambil instance model User yang sedang login
        $user = Auth::user();
        // Mengupdate atribut model User dengan data dari form
        $user->name = $this->formData['name'];
        $user->nim = $this->formData['nim'];
        $user->prodi = $this->formData['prodi'];

        // Menyimpan perubahan ke database
        $user->save();
        Notification::make()
            ->success()
            ->title('Profil berhasil diperbarui!')
            ->send();
        $this->dispatch('reload-page');
    }

    public function cancelEdit(): void
    {
        $this->isEditing = false;
        $this->dispatch('reload-page');
        // $this->form->fill(Auth::user()->toArray());
    }
}