<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getHeading() }}
        </x-slot>

        <x-slot name="description">
            {{ $this->getSubheading() }}
        </x-slot>
    
        <div>
            {{ $this->wizardForm }}
    
            <h3 class="text-lg font-semibold mb-2 mt-6">Pratinjau Teks OCR & Anotasi:</h3>
            <div
                x-data="{
                    ocr: @entangle('ocr'),
                    annotations: @entangle('annotations'),
                    currentDocumentIndexInViewer: @entangle('selectedDocumentIndexForViewer'),
                    dispatchUpdate: function(finalOcr, finalAnnotations) {
                        console.log('Dispatching update for document index:', this.currentDocumentIndexInViewer);
                        $wire.updateDocumentOcrAndAnnotations(
                            this.currentDocumentIndexInViewer,
                            finalOcr,
                            finalAnnotations
                        );
                    },
                    init() {
                        console.log('--- Alpine x-data scope initialized! ---');
                        console.log('--- Alpine init() method running! ---');
    
                        // Panggil renderHighlights setiap kali OCR atau annotations berubah
                        this.$watch('ocr', (newOcr) => {
                            if (newOcr) {
                                console.log('OCR property changed in Alpine! Re-rendering highlights.');
                                this.$nextTick(() => {
                                    renderHighlights();
                                });
                            }
                        });
    
                        this.$watch('annotations', (newAnnotations) => {
                            console.log('Annotations property changed in Alpine! Re-rendering highlights.');
                            this.$nextTick(() => {
                                renderHighlights();
                            });
                        }, { deep: true }); // Watch deeply for changes within the object
    
                        this.$wire.on('ocr-loaded', ({ ocr, extracted_fields }) => {
                            console.log('Livewire ocr-loaded event received for viewer. Updating OCR and annotations.');
                            this.annotations = extracted_fields; // Update annotations
                        });
    
                        // Panggil saat inisialisasi awal jika OCR sudah ada
                        if (this.ocr) {
                            console.log('Initial OCR present. Rendering highlights.');
                            this.$nextTick(() => {
                                renderHighlights();
                            });
                        }
                    }
                }"
                class="border border-gray-300 p-4 rounded-lg bg-white overflow-auto max-h-96"
            >
                <div
                    id="ocr-content"
                    contenteditable="true"
                    class="p-4 min-h-[200px] border rounded bg-gray-50 mb-6 whitespace-pre-wrap"
                    x-html="ocr"
                ></div>
    
                <input type="hidden" id="annotations-input" x-model="annotations" />
    
                <template id="annotation-modal">
                    <div class="modal-overlay fixed inset-0 z-[9999] bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center animate-fade-in-down">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-80 text-center">
                            <h3>Pilih Jenis Kalimat/Kata</h3>
                            <select id="type-dropdown" class="block w-full mt-2 mb-4 border-gray-300 rounded">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="nomor_surat">Nomor Surat</option>
                                <option value="isi_surat">Isi Surat</option>
                                <option value="penanda_tangan">Penanda Tangan</option>
                                <option value="tanggal">Tanggal</option>
                            </select>
                            <div class="flex justify-between">
                                <button onclick="saveAnnotation()"
                                    class="px-4 py-2 bg-[#6C88A4] text-white rounded hover:bg-[#2C3E50] hover:text-white">
                                    Simpan
                                </button>
                                <button type="cancel"
                                    class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-600">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </x-filament::section>
    
    @push('scripts')
        <script>
            console.log('--- Script loaded! ---');
            let currentSelection = null; // Menyimpan seleksi teks saat ini
    
            const typeColors = {
                nomor_surat: "#ffeb3b",
                isi_surat: "#4caf50",
                penanda_tangan: "#2196f3",
                tanggal: "#f57c00",
                nama_ortu: "#9c27b0",
                pekerjaan: "#795548",
                nip: "#607d8b",
                pangkat: "#009688",
                instansi: "#8bc34a",
                thn_akademik: "#ff9800",
                keterangan_surat: "#e91e63",
                jenis_surat: "#00bcd4"
            };
    
            let textNodes = []; 
            let plainTextContent = '';
            let ocrContentDiv = null; 
    
            // Fungsi utilitas untuk meng-escape karakter regex
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }
    
            // Fungsi untuk mendapatkan posisi awal dan panjang seleksi secara global
            function getSelectionGlobalOffsets(selection, container, cleanPlainText) {
                if (!selection || selection.rangeCount === 0 || !container || !cleanPlainText) return null;
    
                const range = selection.getRangeAt(0);
    
                // Buat range dari awal container hingga awal seleksi
                const preSelectionRange = range.cloneRange();
                preSelectionRange.selectNodeContents(container);
                preSelectionRange.setEnd(range.startContainer, range.startOffset);
    
                // Dapatkan teks sebelum seleksi, lalu hitung panjangnya
                const tempDivForPreText = document.createElement('div');
                tempDivForPreText.appendChild(preSelectionRange.cloneContents());
                const preText = tempDivForPreText.textContent || tempDivForPreText.innerText;
    
                const globalStart = preText.length;
                const selectedText = range.toString();
                const globalLength = selectedText.length;
    
                // Verifikasi: Cek apakah teks yang dipilih benar-benar ada di posisi global ini
                const expectedText = cleanPlainText.substring(globalStart, globalStart + globalLength);
                if (expectedText === selectedText) {
                    return { start: globalStart, length: globalLength };
                } else {
                    console.warn(`[getSelectionGlobalOffsets] Verification failed: Expected "${expectedText}", got "${selectedText}". Attempting fallback.`);
                    const fallbackStart = cleanPlainText.indexOf(selectedText);
                    if (fallbackStart !== -1) {
                         console.warn(`[getSelectionGlobalOffsets] Fallback successful. Found at ${fallbackStart}.`);
                         return { start: fallbackStart, length: selectedText.length };
                    }
                    console.error("[getSelectionGlobalOffsets] Fallback failed: Selected text not found in plainTextContent.");
                    return null;
                }
            }
    
    
            // Inisialisasi/inisialisasi ulang textNodes dan plainTextContent
            function initTextNodes(initialOcrHtml = null) {
                if (!ocrContentDiv) {
                    console.error("[initTextNodes] ocrContentDiv is not set.");
                    return;
                }
    
                // Langkah 1: Reset DOM ocrContentDiv ke konten OCR MURNI
                if (initialOcrHtml) {
                    ocrContentDiv.innerHTML = initialOcrHtml;
                } else {
                    // Jika tidak ada initialOcrHtml, ambil dari x-data.ocr
                    const alpineDataScope = Alpine.$data(ocrContentDiv.closest('[x-data]'));
                    ocrContentDiv.innerHTML = alpineDataScope.ocr;
                }
    
                // Langkah 2: Hitung plainTextContent dari DOM yang sudah bersih
                plainTextContent = ocrContentDiv.textContent || ocrContentDiv.innerText || "";
    
                // Langkah 3: Bangun ulang peta node teks dari DOM aktual (setelah bersih)
                textNodes = [];
                let accumulatedLength = 0;
    
                const walker = document.createTreeWalker(
                    ocrContentDiv,
                    NodeFilter.SHOW_TEXT,
                    null 
                );
    
                while (walker.nextNode()) {
                    const node = walker.currentNode;
                    textNodes.push({
                        node: node,
                        offset: accumulatedLength
                    });
                    accumulatedLength += node.nodeValue.length;
                }
                console.log('[initTextNodes] Completed. plainTextContent length:', plainTextContent.length, 'textNodes count:', textNodes.length);
            }
    
            // Fungsi untuk menemukan semua kecocokan teks (digunakan oleh renderHighlights)
            function findMatches(textToFind) {
                if (!plainTextContent || !textToFind) {
                    return [];
                }
                const safeText = escapeRegExp(textToFind);
                const regex = new RegExp(safeText, 'gi');
                const matches = [];
    
                let match;
                while ((match = regex.exec(plainTextContent)) !== null) {
                    matches.push({
                        start: match.index,
                        length: match[0].length
                    });
                }
                return matches;
            }
    
            // Fungsi untuk membungkus teks dengan tag <mark> pada posisi tertentu
            function wrapTextByPosition(textToHighlight, start, length, type) {
                if (!ocrContentDiv || !textToHighlight || typeof start === 'undefined' || typeof length === 'undefined' || !type) {
                    console.error("[wrapTextByPosition] Invalid arguments. Skipping.");
                    return;
                }
                if (start < 0 || start + length > plainTextContent.length) {
                    console.error(`[wrapTextByPosition] Invalid range: start=${start}, length=${length}. plainTextContent length: ${plainTextContent.length}. Skipping.`);
                    return;
                }
                 // Double check if the segment of plainTextContent matches the textToHighlight
                const segment = plainTextContent.substring(start, start + length);
                if (segment !== textToHighlight) {
                    console.warn(`[wrapTextByPosition] Text mismatch at calculated position. Expected "${textToHighlight}", found "${segment}". Attempting to proceed but might be inaccurate.`);
                }
    
    
                for (const info of textNodes) {
                    const nodeStart = info.offset;
                    const nodeEnd = info.offset + info.node.nodeValue.length;
    
                    // Cek apakah rentang highlight sepenuhnya berada di dalam node teks ini
                    if (start >= nodeStart && start < nodeEnd && (start + length) <= nodeEnd) {
                        const range = document.createRange();
                        range.setStart(info.node, start - nodeStart);
                        range.setEnd(info.node, start - nodeStart + length);
    
                        // Hindari menyoroti bagian yang sudah ditandai, jika ada
                        if (range.commonAncestorContainer.closest && range.commonAncestorContainer.closest('mark')) {
                            console.warn("[wrapTextByPosition] Skipping highlight: part already marked.");
                            return;
                        }
    
                        const mark = document.createElement("mark");
                        mark.setAttribute("data-type", type);
                        mark.style.backgroundColor = typeColors[type] || "#ccc"; 
                        mark.textContent = textToHighlight; 
    
                        try {
                            range.deleteContents(); 
                            range.insertNode(mark); 
                            console.log(`[wrapTextByPosition] Wrapped "${textToHighlight}" (type: ${type}) at start ${start}.`);
                        } catch (e) {
                            console.error(`[wrapTextByPosition] Error inserting node for "${textToHighlight}" (type: ${type}) at start ${start}:`, e);
                        }
                        return; 
                    }
                }
                console.warn(`[wrapTextByPosition] Could not wrap text "${textToHighlight}" (type: ${type}) at start ${start} length ${length}. Node not found or range invalid within textNodes map.`);
            }
    
            // Fungsi untuk merender semua highlight dari data annotations
            function renderHighlights() {
                console.log('[renderHighlights] called.');
                if (!ocrContentDiv) {
                    console.error("[renderHighlights] ocrContentDiv not set. Skipping.");
                    return;
                }
    
                // 1. Dapatkan konten OCR asli dari Alpine Scope (pastikan tidak ada highlight)
                const alpineDataScope = Alpine.$data(ocrContentDiv.closest('[x-data]'));
                const originalOcrHtml = alpineDataScope.ocr;
    
                // 2. SELALU bersihkan DOM dan bangun ulang textNodes dari konten OCR asli
                initTextNodes(originalOcrHtml); 
    
                const annotationsObject = alpineDataScope.annotations || {};
                console.log('[renderHighlights] Annotations to render:', annotationsObject);
    
                // 3. Ubah annotations menjadi array dan urutkan berdasarkan posisi 'start' secara DESCENDING
                const sortedAnnotations = Object.entries(annotationsObject)
                    .map(([key, data]) => ({ key, ...data }))
                    .filter(a => typeof a.start === 'number' && typeof a.length === 'number' && typeof a.text === 'string' && a.text.length > 0)
                    .sort((a, b) => b.start - a.start); // Urutkan dari akhir ke awal
    
                console.log('[renderHighlights] Sorted Annotations:', sortedAnnotations);
    
                // 4. Terapkan highlight
                sortedAnnotations.forEach(annotation => {
                    wrapTextByPosition(
                        annotation.text,
                        annotation.start,
                        annotation.length,
                        annotation.key
                    );
                });
                console.log('[renderHighlights] Highlighting process finished.');
            }
    
            // Fungsi untuk menyimpan anotasi baru setelah seleksi
            window.saveAnnotation = function() {
                const selectedType = document.getElementById("type-dropdown").value;
                const selection = window.getSelection();
    
                if (!selectedType || !selection || selection.rangeCount === 0 || selection.isCollapsed) {
                    alert("Silakan pilih jenis anotasi dan pastikan ada teks yang terpilih.");
                    return;
                }
                if (!ocrContentDiv) {
                    console.error("[saveAnnotation] ocrContentDiv not set. Cannot save.");
                    return;
                }
    
                // Ini memastikan perhitungan offset berdasarkan DOM yang bersih.
                initTextNodes();
    
                const globalOffsets = getSelectionGlobalOffsets(selection, ocrContentDiv, plainTextContent);
    
                if (!globalOffsets) {
                    alert("Gagal mendapatkan posisi teks yang dipilih. Coba lagi.");
                    document.querySelectorAll(".modal-overlay").forEach(el => el.remove());
                    return;
                }
    
                const { start: startIndex, length: selectedLength } = globalOffsets;
                const selectedText = selection.toString().trim();
    
                if (!selectedText) {
                    document.querySelectorAll(".modal-overlay").forEach(el => el.remove());
                    return;
                }
    
                const alpineDataScope = Alpine.$data(ocrContentDiv.closest('[x-data]'));
    
                // Perbarui objek annotations di Alpine (yang akan dikirim ke Livewire)
                let updatedAnnotationsObject = { ...alpineDataScope.annotations };
                updatedAnnotationsObject[selectedType] = {
                    text: selectedText,
                    start: startIndex,
                    length: selectedLength
                };
                console.log('[saveAnnotation] Annotation to save:', updatedAnnotationsObject[selectedType]);
    
                // Dispatch update ke Livewire. Alpine watcher akan memicu renderHighlights.
                alpineDataScope.dispatchUpdate(
                    ocrContentDiv.textContent,
                    updatedAnnotationsObject
                );
    
                document.querySelectorAll(".modal-overlay").forEach(el => el.remove());
                currentSelection = null;
            };
    
            // Fungsi untuk menampilkan modal pemilihan jenis anotasi
            window.showModalAtPosition = function(range) {
                document.querySelectorAll(".modal-overlay").forEach(el => el.remove());
    
                const modalOverlay = document.createElement("div");
                modalOverlay.className = "modal-overlay flex items-center justify-center";

                console.log('sampai sini');
    
                const template = document.getElementById("annotation-modal");
                const modalContent = template.content.cloneNode(true);
                modalOverlay.appendChild(modalContent);
                document.body.appendChild(modalOverlay);
    
                modalOverlay.addEventListener("click", (e) => {
                    if (e.target === modalOverlay) {
                        modalOverlay.remove();
                    }
                });
            };
    
            // Event listener DOMContentLoaded untuk inisialisasi awal
            document.addEventListener("DOMContentLoaded", () => {
                ocrContentDiv = document.getElementById("ocr-content"); 
                if (!ocrContentDiv) {
                    console.error("Editable div #ocr-content not found on DOMContentLoaded.");
                    return;
                }
    
                // Inisialisasi awal plainTextContent dan textNodes.
                initTextNodes();
    
                ocrContentDiv.addEventListener("mouseup", () => {
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

            });
        </script>
    @endpush
</x-filament-panels::page>