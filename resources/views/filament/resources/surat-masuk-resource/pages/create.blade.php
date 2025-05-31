<x-filament-panels::page>
    
    <x-filament-panels::form>
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>
    <div wire:poll.2s="pollForOCRResult" style="display: none;"></div>
    <div 
        x-data="{ isLoading: false }" 
        x-on:show-loading.window="isLoading = true" 
        x-on:hide-loading.window="isLoading = false"
    >

    <template x-if="isLoading">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm" style="backdrop-filter: blur(4px);">
            <div class="bg-white rounded-lg shadow-xl p-6 w-80 text-center animate-fade-in-down">
                <!-- Loader -->
                <div class="animate-spin h-12 w-12 border-t-2 border-b-2 border-blue-500 mx-auto rounded-full"></div>

                <!-- Text -->
                <h2 class="mt-4 text-lg font-semibold">Sedang Memproses...</h2>
                <p class="text-sm text-gray-500 mt-1">Mohon tunggu sebentar</p>
            </div>
        </div>
    </template>
    </div>
</x-filament-panels::page>
