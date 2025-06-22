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

    {{-- === MODAL INFORMASI KUSTOM BARU === --}}
    <div x-data="{ 
        showInfoModal: false, 
        infoModalTitle: '', 
        infoModalDescription: '', 
        infoModalDetails: '',
        init() {
            // Listener untuk event dari Livewire (PHP)
            this.$wire.on('show-custom-info-modal', (data) => {
                this.infoModalTitle = data[0].title;
                this.infoModalDescription = data[0].description;
                this.infoModalDetails = data[0].details || ''; // Pastikan ada default jika 'details' opsional
                this.showInfoModal = true;
            });
        }
    }"
    x-show="showInfoModal" 
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    class="fixed inset-0 z-[10000] bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center">
        <div @click.away="showInfoModal = false" {{-- Tutup modal saat klik di luar --}}
             class="bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-md text-center relative">
            
            {{-- Tombol Silang --}}
            <button @click="showInfoModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <h3 x-text="infoModalTitle" class="text-xl font-bold mb-3 text-gray-900"></h3>
            <p x-text="infoModalDescription" class="mb-4 text-gray-700"></p>
            <p x-show="infoModalDetails" x-text="infoModalDetails" class="text-sm text-gray-500 italic mb-4"></p>
            
            {{-- Tombol Tutup (opsional, jika ingin ada tombol selain silang) --}}
            <button @click="showInfoModal = false" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none">
                Tutup
            </button>
        </div>
    </div>
    {{-- === AKHIR MODAL INFORMASI KUSTOM BARU === --}}

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
