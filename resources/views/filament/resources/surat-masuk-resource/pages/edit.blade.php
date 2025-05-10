<x-filament-panels::page>
    <x-filament::form wire:submit="save">
        {{ $this->form }}
    </x-filament::form>

    <!-- Area Preview OCR -->
    <div id="ocr-content" contenteditable="true" class="p-4 min-h-[200px] border rounded bg-gray-50 mt-6 mb-6">
        {!! nl2br(e(Session::get('flask_response.ocr_text'))) !!}
    </div>

    @push('scripts')
        <script>
            const flaskResponse = @json(Session::get('flask_response'));

            document.addEventListener('DOMContentLoaded', () => {
                const ocrContent = document.getElementById('ocr-content');
                const extracted = flaskResponse.extracted_fields;

                if (!ocrContent || !extracted) return;

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
