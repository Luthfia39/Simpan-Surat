<x-filament-panels::page>
    <div>
        <h2 class="text-xl font-semibold mb-4">Profil Pengguna</h2>

        <div class="space-y-4">
            {{ $this->form }}

            <div class="flex justify-end gap-3">
                @if ($this->isEditing)
                    <x-filament::button type="button" wire:click="cancelEdit">
                        Batal
                    </x-filament::button>
                    <x-filament::button type="submit" wire:click="save">
                        Simpan Perubahan
                    </x-filament::button>
                @else
                    <x-filament::button type="button" wire:click="editProfile">
                        Ubah
                    </x-filament::button>
                @endif
            </div>
        </div>
    </div>
    @script
        <script>
            Livewire.on('reload-page', () => {
                window.location.href = '/user/profile';
            });
        </script>
    @endscript
</x-filament-panels::page>