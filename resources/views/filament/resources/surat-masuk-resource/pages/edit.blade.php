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
                isi_surat: "#4caf50",       // hijau
                penanda_tangan: "#2196f3",  // biru
                tanggal: "#f57c00"          // oranye
            };
        
            let fullText = '';
            let textNodes = [];
        
            function escapeRegExp(string) {
                return string.replace(/[*+?^${}()|[\]\\]/g, '\\$&');
            }
        
            // Inisialisasi textNodes dan fullText saat DOM ready
            function initTextNodes() {
                const container = document.getElementById("ocr-content");
                textNodes = [];
                let accumulatedLength = 0;
        
                const walker = document.createTreeWalker(
                    container,
                    NodeFilter.SHOW_TEXT,
                    { acceptNode: () => NodeFilter.FILTER_ACCEPT }
                );
        
                while (walker.nextNode()) {
                    const node = walker.currentNode;
                    textNodes.push({
                        node: node,
                        offset: accumulatedLength
                    });
                    accumulatedLength += node.nodeValue.length;
                }
        
                fullText = accumulatedLength;
            }
        
            // Cari posisi teks dalam fullText
            function findMatches(text) {
                const safeText = escapeRegExp(text);
                const regex = new RegExp(safeText, 'gi');
                const matches = [];

                console.log('fullText:', fullText);
        
                let match;
                while ((match = regex.exec(ocr_text)) !== null) {
                    matches.push({
                        start: match.index,
                        length: match[0].length
                    });
                }
        
                return matches;
            }
        
            // Wrap teks berdasarkan posisi global
            function wrapTextByPosition(container, textToHighlight, start, length, type) {
                for (const info of textNodes) {
                    const nodeStart = info.offset;
                    const nodeEnd = info.offset + info.node.nodeValue.length;
        
                    if (
                        start >= nodeStart &&
                        start + length <= nodeEnd
                    ) {
                        const range = document.createRange();
                        range.setStart(info.node, start - nodeStart);
                        range.setEnd(info.node, start - nodeStart + length);
        
                        const mark = document.createElement("mark");
                        mark.setAttribute("data-type", type);
                        mark.style.backgroundColor = typeColors[type];
                        mark.textContent = textToHighlight;
        
                        range.deleteContents();
                        range.insertNode(mark);
        
                        break;
                    }
                }
            }
        
            // Render semua anotasi saat reload
            function renderHighlights() {
                const container = document.getElementById("ocr-content");
        
                // Bersihkan semua <mark> lama
                container.querySelectorAll("mark").forEach(mark => {
                    const textNode = document.createTextNode(mark.textContent);
                    mark.parentNode.replaceChild(textNode, mark);
                });
        
                // Loop semua anotasi dan wrap
                annotations.forEach(annotation => {
                    const key = Object.keys(annotation)[0];
                    const value = annotation[key];

                    console.log(key, value);
        
                    if (!value || typeof value !== 'string') return;
        
                    const matches = findMatches(value);

                    console.log('matches', matches);
        
                    matches.forEach(match => {
                        wrapTextByPosition(
                            container,
                            value,
                            match.start,
                            match.length,
                            key
                        );
                    });
                });
            }
        
            // Event listener untuk seleksi teks
            function saveAnnotation() {
                const selectedType = document.getElementById("type-dropdown").value;
        
                if (!selectedType || !currentSelection) {
                    alert("Silakan pilih jenis anotasi.");
                    return;
                }
        
                const { range } = currentSelection;
                const selectedText = range.toString().trim();
        
                if (!selectedText) return;
        
                // Buat mark baru
                const mark = document.createElement("mark");
                mark.setAttribute("data-type", selectedType);
                mark.style.backgroundColor = typeColors[selectedType];
                mark.textContent = selectedText;
        
                // Ganti teks dengan mark
                range.deleteContents();
                range.insertNode(mark);
        
                // Simpan ke annotations sebagai object { type: text }
                annotations = annotations.filter(ann => !ann.hasOwnProperty(selectedType));
                annotations.push({ [selectedType]: selectedText });
        
                console.log('Anotasi tersimpan:', annotations);
        
                // Hapus modal
                document.querySelectorAll(".modal-overlay").forEach(el => el.remove());
                currentSelection = null;
            }
        
            // Modal untuk pilih jenis anotasi
            function showModalAtPosition(range) {
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
        
                // Close saat klik overlay
                modalOverlay.addEventListener("click", (e) => {
                    if (e.target === modalOverlay) {
                        modalOverlay.remove();
                    }
                });
            }
        
            // Event Listener DOM Loaded
            document.addEventListener("DOMContentLoaded", () => {
                Livewire.on('ocr-loaded', (data) => {
                    const { ocr, extracted_fields } = data[0];
                    ocr_text = ocr;
        
                    // Hanya ambil type dan text dari anotasi
                    if (typeof extracted_fields === 'string') {
                        try {
                            const parsedFields = JSON.parse(extracted_fields);
                            annotations = Object.entries(parsedFields).map(([key, value]) => ({ [key]: value.text }));
                        } catch (e) {
                            console.error('Gagal parse extracted_fields:', e);
                            annotations = [];
                        }
                    } else {
                        annotations = Object.entries(extracted_fields).map(([key, value]) => ({
                            [key]: value[0].text
                        }));
                    }

                    console.log('Annotations:', annotations);
        
                    // Isi konten OCR
                    document.getElementById("ocr-content").innerHTML = ocr_text;
        
                    // Inisialisasi textNodes dan fullText
                    initTextNodes();
        
                    // Render semua anotasi
                    renderHighlights();
                });
        
                const editableDiv = document.getElementById("ocr-content");
        
                // Seleksi teks untuk anotasi baru
                editableDiv.addEventListener("mouseup", () => {
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0 && !selection.isCollapsed) {
                        const range = selection.getRangeAt(0);
                        const parentMark = range.commonAncestorContainer.closest?.("mark");
        
                        if (parentMark) {
                            alert("Bagian ini sudah di-highlight.");
                            return;
                        }
        
                        currentSelection = {
                            text: selection.toString(),
                            range: range
                        };
        
                        showModalAtPosition(range);
                    }
                });
        
                Livewire.on('update-data', ocr => {
                    const ocr_final = getPlainTextFromContent();
                    Livewire.dispatch('data-ready', { ocr_final, annotations })
                });
            });
        
            // Fungsi ekstrak teks bersih
            function getPlainTextFromContent() {
                const container = document.getElementById("ocr-content");
                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = ocr_text;
                return tempDiv.textContent || tempDiv.innerText || "";
            }
        </script>
    @endpush
</x-filament-panels::page>