<x-filament-panels::page>
    <x-filament::form wire:submit="save">
        {{ $this->form }}
    </x-filament::form>

    <!-- Area Preview OCR -->
    <div id="ocr-content" contenteditable="true" class="p-4 min-h-[200px] border rounded bg-gray-50 mt-6 mb-6">
        @if ($this->form->getState()['ocr_text'] ?? false)
            {!! nl2br(e($this->form->getState()['ocr_text'])) !!}
        @else
            OCR text tidak tersedia.
        @endif
    </div>

    @push('scripts')
        <script>
            // Ambil data dari PHP ke JS
            const flaskResponse = {
                extracted_fields: @json($this->form->getState()['extracted_fields'] ?? []),
                ocr_text: @json($this->form->getState()['ocr_text'] ?? '')
            };

            document.addEventListener('DOMContentLoaded', () => {
                const ocrContent = document.getElementById('ocr-content');
                const extracted = flaskResponse.extracted_fields;

                if (!ocrContent || !extracted) return;

                // Highlight semua field di extracted_fields
                Object.values(extracted).forEach(val => {
                    if (!val) return;

                    const regex = new RegExp(`(${val})`, 'gi');
                    ocrContent.innerHTML = ocrContent.innerHTML.replace(regex,
                        `<mark style="background-color:#fff3cd">$1</mark>`);
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
