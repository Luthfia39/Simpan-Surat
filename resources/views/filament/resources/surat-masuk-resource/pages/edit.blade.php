<x-filament-panels::page>
    <x-filament-panels::form>
        {{ $this->form }}

        <!-- Area Preview OCR -->
        <div 
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
            let ocr_text = '';
            let currentSelection = null;
            let annotations = [];

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

                    ocr_text = content;

                    renderHighlights();
                }, interval);
            });

            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function renderHighlights() {

                annotations.forEach((ann) => {
                    const key = Object.keys(ann);
                    const value = ann[key];

                    console.log('key : ', key, 'value : ', value);

                    if (!value || typeof value !== 'string') return;

                    const safeValue = escapeRegExp(value);
                    const regex = new RegExp(safeValue, 'gi');

                    ocr_text = ocr_text.replace(regex, (match) => {
                        return `<mark data-type="${key}" style="background-color:${typeColors[key] || '#ffff00'}">${match}</mark>`;
                    });
                });

                const container = document.getElementById("ocr-content");
                container.innerHTML = ocr_text;
            }

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

                renderHighlights();

                // Hapus semua modal
                document.querySelectorAll(".modal-overlay").forEach(el => el.remove());

                // Reset seleksi
                currentSelection = null;

                console.log('hasil seleksi : ', annotations);
            }

            function getPlainTextFromContent() {
                // Buat elemen sementara untuk parsing
                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = ocr_text;

                // Hilangkan semua <mark>, biarkan teks saja
                return tempDiv.textContent || tempDiv.innerText || "";
            }

            // Event Listener untuk highlight
            document.addEventListener("DOMContentLoaded", () => {
                Livewire.on('ocr-loaded',  (data) => {
                    const { ocr, extracted_fields } = data[0];
                    ocr_text = ocr;
                    annotations = Object.entries(extracted_fields).map(([key, value]) => {
                        return { [key]: value[0] };
                    });
                    document.getElementById("ocr-content").innerHTML = ocr;
                    renderHighlights();
                })
                
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

                Livewire.on('update-data', ocr => {
                    const ocr_final = getPlainTextFromContent();
                    Livewire.dispatch('data-ready', {ocr_final, annotations})
                })
            });
        </script>
    @endpush
</x-filament-panels::page>