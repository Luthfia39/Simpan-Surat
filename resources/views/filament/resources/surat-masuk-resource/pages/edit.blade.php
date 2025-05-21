<x-filament-panels::page>
    <x-filament-panels::form>
        {{ $this->form }}

        <!-- Area Preview OCR -->
        <div 
            wire:key="ocr-editor"
            id="ocr-content" 
            contenteditable="true"
            class="p-4 min-h-[200px] border rounded bg-gray-50 mb-6 whitespace-pre-wrap"
        >
        </div>

        <input type="hidden" id="annotations-input" wire:model="annotations" />

        <!-- Template Modal untuk Anotasi -->
        <template id="annotation-modal">
            <div class="modal-overlay fixed z-50 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center animate-fade-in-down">
                <div class="bg-white rounded-lg shadow-xl p-6 w-80 text-center">
                    <h3>Pilih Jenis Kalimat/Kata</h3>
                    <select id="type-dropdown" class="block w-full mt-2 mb-4 border-gray-300 rounded">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="nomor_surat">Nomor Surat</option>
                        <option value="isi_surat">Isi Surat</option>
                        <option value="penanda_tangan">Penanda Tangan</option>
                        <option value="tanggal">Tanggal</option>
                    </select>
                    <button onclick="saveAnnotation()"
                            class="px-4 py-2 border-black text-black rounded hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </div>
        </template>

        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>

    @push('scripts')
        <script>
            let currentSelection = null;

            // Mapping jenis → warna
            const typeColors = {
                nomor_surat: "#ffeb3b",     // kuning
                isi_surat: "#4caf50",     // hijau
                penanda_tangan: "#2196f3",       // biru
                tanggal: "#f57c00"      // oranye
            };
            
            let typingTimer;
            const interval = 500;

            document.getElementById("ocr-content").addEventListener("input", () => {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    const content = document.getElementById("ocr-content").innerHTML;

                    Livewire.dispatch('updateOcrText', {
                        ocr_text: content
                    });
                }, interval);
            });

            function showModalAtPosition(range) {
                // Hapus semua modal sebelum menampilkan yang baru
                document.querySelectorAll(".modal-overlay").forEach(el => el.remove());

                const rect = range.getBoundingClientRect();

                const modalOverlay = document.createElement("div");
                modalOverlay.className = "modal-overlay fixed z-50 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center";
                modalOverlay.style.top = `${rect.top - 100}px`;
                modalOverlay.style.left = `${rect.left + window.scrollX}px`;
                modalOverlay.style.width = `${rect.width}px`;
                modalOverlay.style.height = `${rect.height}px`;

                const template = document.getElementById("annotation-modal");
                const modalContent = template.content.cloneNode(true);
                modalOverlay.appendChild(modalContent);

                document.body.appendChild(modalOverlay);

                // Close modal saat klik overlay
                modalOverlay.addEventListener("click", (e) => {
                    if (e.target === modalOverlay) {
                        modalOverlay.remove();
                    }
                });
            }

            const annotations = [];

            function saveAnnotation() {
                const selectedType = document.getElementById("type-dropdown").value;
                if (!selectedType || !currentSelection) {
                    alert("Silakan pilih jenis terlebih dahulu.");
                    return;
                }

                const { range } = currentSelection;

                if (!range || range.collapsed) return;

                const selectedText = range.toString();

                annotations.push({ [selectedType]: selectedText });

                // Buat elemen mark untuk highlight
                const mark = document.createElement("mark");
                const color = typeColors[selectedType] || "#ffffff";
                mark.style.backgroundColor = color;
                mark.dataset.annotationType = selectedType;

                // Bungkus teks yang dipilih dengan mark
                range.surroundContents(mark);

                // Update hidden input dengan JSON string
                const input = document.getElementById("annotations-input");
                input.value = JSON.stringify(annotations);
                input.dispatchEvent(new Event('input'));

                // Hapus semua modal
                document.querySelectorAll(".modal-overlay").forEach(el => el.remove());

                // Reset seleksi
                currentSelection = null;

                console.log('hasil seleksi : ', annotations);
            }

            // Event Listener untuk highlight
            document.addEventListener("DOMContentLoaded", () => {
                const editableDiv = document.getElementById("ocr-content");

                editableDiv.addEventListener("mouseup", () => {
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0 && !selection.isCollapsed) {
                        const range = selection.getRangeAt(0);
                        const parentMark = range.commonAncestorContainer.closest ? range.commonAncestorContainer.closest("mark") : null;

                        if (!parentMark) {
                            currentSelection = {
                                text: range.toString(),
                                range: range
                            };

                            showModalAtPosition(range);
                        }
                    }
                });
            });
        </script>
    @endpush
</x-filament-panels::page>