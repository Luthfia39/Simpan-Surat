<x-filament-panels::page>
    <div x-data="{ isLoading: false }" x-on:show-loading.window="isLoading = true" x-on:hide-loading.window="isLoading = false">

        <template x-if="isLoading">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-lg shadow-lg p-6 w-80 text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary mx-auto"></div>
                    <h2 class="mt-4 text-lg font-semibold">Sedang Memproses...</h2>
                    <p class="text-sm text-gray-500">Mohon tunggu sebentar</p>
                </div>
            </div>
        </template>
    </div>
    <x-filament-panels::form>
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>
</x-filament-panels::page>
