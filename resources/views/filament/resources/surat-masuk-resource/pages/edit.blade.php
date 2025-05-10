<x-filament-panels::page>
    <!-- Area Editable -->
    <!-- <div id="ocr-content" contenteditable="true" class="p-4 min-h-[200px] border rounded bg-gray-50 mb-6">
        {!! nl2br(e("Nomor\nLamp\nHal\n\nKepada :\n\nUNIVERSITAS GADJAH MADA\nSEKOLAH VOKASI ee")) !!}
    </div> -->

    <!-- Form Wizard -->
    <x-filament-panels::form wire:submit="submit">
        {{ $this->form }}
    </x-filament-panels::form>

    @push('scripts')
    <script>
        const ocrContent = document.getElementById('ocr-content');

        // Daftar field yang bisa auto-highlight + warna
        const highlightFields = {
            'nomor_surat': '#fff3cd',     // kuning muda
            'isi_ringkas': '#d4edda',     // hijau muda
            'penandatangan': '#cce5ff'    // biru muda
        };

        // Event listener untuk seleksi teks
        ocrContent.addEventListener('mouseup', () => {
            const selection = window.getSelection();
            const range = selection.getRangeAt(0);

            const selectedText = selection.toString().trim();
            console.log('select', selectedText)
            console.log('active', activeField)

            // Wrap teks yang dipilih dengan mark
            const span = document.createElement('mark');
            span.style.backgroundColor = 'red';
            span.textContent = selectedText;

            console.log('span', span)

            range.deleteContents();
            range.insertNode(span);

            // Reset seleksi
            selection.removeAllRanges();
        });

        function showModal() {
            const modal = `
            <div class="fixed inset-0 flex items-center justify-center z-50">
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Modal Title</h2>
                    <p class="mb-4">Modal content goes here.</p>
                    <button class="px-4 py-2 bg-blue-500 text-white rounded">Close</button>
                </div>
            </div>
            `
        }
    </script>
    @endpush
</x-filament-panels::page>