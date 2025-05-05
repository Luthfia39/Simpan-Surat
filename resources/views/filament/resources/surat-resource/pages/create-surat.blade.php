<x-filament-panels::page>
    <form wire:submit.prevent="processFile">
        {{ $this->form }}

        <div class="mt-4">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                Unggah dan Proses File
            </button>
        </div>
    </form>
</x-filament-panels::page>