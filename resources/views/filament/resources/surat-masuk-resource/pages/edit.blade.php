<x-filament-panels::page>
    <x-filament-panels::form wire:submit="callHook('onProcessSave')"> {{-- Gunakan wire:submit untuk memanggil aksi simpan --}}
        {{ $this->form }}

        <p class="text-sm font-semibold">Pratinjau Teks OCR & Anotasi:</p>
        
        <div class="p-3 bg-gray-100 rounded-md">
            <p class="text-sm font-medium text-gray-700 mb-2">Keterangan Warna Anotasi:</p>
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm">
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full mr-1" style="background-color: #ffeb3b;"></span>
                    Nomor Surat
                </span>
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full mr-1" style="background-color: #4caf50;"></span>
                    Isi Surat
                </span>
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full mr-1" style="background-color: #2196f3;"></span>
                    Penanda Tangan
                </span>
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full mr-1" style="background-color: #f57c00;"></span>
                    Tanggal
                </span>
                <span class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full mr-1" style="background-color: #a578f5;"></span>
                    Penerima/Pengirim
                </span>
            </div>
            <p class="text-sm mt-2">
                Langkah - langkah anotasi teks dapat dilihat pada
                <span>
                    <a href="javascript:void(0)" onclick="showOcrHelpModal()" style="text-decoration: underline; color:rgb(22, 140, 237)">
                        panduan anotasi teks OCR.
                    </a>
                </span>
            </p>
        </div>

        <div class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <div 
                    id="ocr-viewer-container"
                    x-data="{
                        // Properti Alpine di-entangle langsung dari Livewire
                        ocr: @entangle('ocr'),
                        annotations: @entangle('annotations'),

                        // Method untuk mengirim update ke Livewire
                        // Ini dipanggil oleh saveAnnotation() dan click MARK delete
                        dispatchUpdate: function(finalOcr, finalAnnotations) {
                            console.log('Dispatching update to Livewire. OCR length:', finalOcr.length, 'Annotations count:', Object.keys(finalAnnotations).length);
                            
                            let dataToSend = finalAnnotations;
                            if (typeof finalAnnotations === 'string') {
                                try {
                                    dataToSend = JSON.parse(finalAnnotations);
                                } catch (e) {
                                    console.error('Failed to parse annotations string:', e);
                                    // Handle error, maybe send empty array or original string
                                    dataToSend = {}; 
                                }
                            }
                                
                            // Panggil updateDocumentOcrAndAnnotations di PHP (nama method di EditSuratMasuks.php)
                            // Tidak perlu documentIndex, karena PHP sudah tahu record mana yang sedang diedit ($this->record)
                            $wire.updateData( 
                                finalOcr,
                                dataToSend
                            );
                        },
                        init() {
                            console.log('--- Alpine x-data scope initialized! ---');
                            
                            // Watcher untuk properti 'ocr' di Alpine
                            this.$watch('ocr', (newOcr) => {
                                if (newOcr) {
                                    console.log('OCR property changed in Alpine! Re-rendering highlights.');
                                    this.$nextTick(() => {
                                        // Pastikan ocrContentDiv terdefinisi sebelum memanggil renderHighlights
                                        ocrContentDiv = document.getElementById('ocr-content'); 
                                        renderHighlights();
                                    });
                                }
                            });

                            // Watcher untuk properti 'annotations' di Alpine
                            this.$watch('annotations', (newAnnotations) => {
                                console.log('Annotations property changed in Alpine! Re-rendering highlights.');
                                this.$nextTick(() => {
                                    // Pastikan ocrContentDiv terdefinisi sebelum memanggil renderHighlights
                                    ocrContentDiv = document.getElementById('ocr-content'); 
                                    renderHighlights();
                                });
                            }, { deep: true }); // Watch deeply for changes within the object
                            
                            // Event Livewire yang menerima data OCR dan anotasi dari backend
                            // Dipicu dari mount() PHP
                            this.$wire.on('ocr-loaded', ({ ocr, extracted_fields }) => {
                                console.log('Livewire ocr-loaded event received for viewer. Updating OCR and annotations.');
                                this.annotations = extracted_fields; // PHP sudah kirim sebagai array/object yang benar
                                this.ocr = ocr; 
                            });

                            // Listener untuk event dari Livewire setelah penyimpanan selesai (updateData di PHP)
                            this.$wire.on('document-update-completed', () => {
                                console.log('Livewire: document-update-completed event received. Starting delayed highlight render.');
                                setTimeout(() => {
                                    // Pastikan ocrContentDiv terdefinisi sebelum memanggil renderHighlights
                                    ocrContentDiv = document.getElementById('ocr-content'); 
                                    renderHighlights(); 
                                    console.log('Delayed renderHighlights finished.');
                                }, 500); 
                            });
                            
                            // Event Listener untuk Perubahan Input Teks di OCR Div
                            this.$nextTick(() => {
                                ocrContentDiv = document.getElementById('ocr-content'); // Inisialisasi ocrContentDiv di sini juga
                                if (ocrContentDiv) {
                                    // Initial render highlights jika data OCR sudah ada saat inisialisasi Alpine
                                    if (this.ocr) {
                                        console.log('Initial OCR present in Alpine. Rendering highlights.');
                                        renderHighlights();
                                    }

                                    // Listener untuk perubahan teks manual di div contenteditable
                                    ocrContentDiv.addEventListener('input', () => {
                                        console.log('OCR content changed in Alpine! Preparing to sync...');

                                        // Ambil innerHTML yang baru, bersihkan semua tag <mark>
                                        const tempDiv = document.createElement('div');
                                        tempDiv.innerHTML = ocrContentDiv.innerHTML;
                                        tempDiv.querySelectorAll('mark').forEach(mark => {
                                            mark.parentNode.replaceChild(document.createTextNode(mark.textContent), mark);
                                        });
                                        
                                        // Update property 'ocr' di Alpine. Ini akan memicu watcher dan renderHighlights.
                                        this.ocr = tempDiv.innerHTML; 
                                        
                                        // Kirim perubahan OCR (teks yang diedit) dan anotasi ke Livewire.
                                        this.dispatchUpdate(this.ocr, this.annotations); 
                                        renderHighlights();
                                    });
                                }
                            });
                        }
                    }">

                        <div
                            id="ocr-content"
                            contenteditable="true"
                            class="p-4 min-h-[200px] border rounded bg-gray-50 whitespace-pre-wrap"
                            x-html="ocr" 
                        ></div>
                    
                        <input type="hidden" id="annotations-input" x-model="annotations" /> 
                </div>
            </div>
            <div class="flex-1"> 
                <div class="white-space-pre-wrap border border-gray-300 rounded-lg bg-white overflow-hidden h-full">
                    <iframe 
                        src="{{ asset('storage/suratMasuk/' . $this->record->pdf_url) }}" 
                        class="w-full h-full" 
                        frameborder="0"
                    ></iframe>
                </div>
            </div>
        </div>
        
        <template id="annotation-modal">
            <div class="modal-overlay fixed inset-0 z-[9999] bg-gray-500 bg-opacity-60 backdrop-blur-sm flex items-center justify-center animate-fade-in-down">
                <div class="bg-white rounded-lg shadow-xl p-6 w-80 text-center">
                    <h3>Pilih Jenis Kalimat/Kata</h3>
                    <select id="type-dropdown" class="block w-full mt-2 mb-4 border-gray-300 rounded">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="nomor_surat">Nomor Surat</option>
                        <option value="isi_surat">Isi Surat</option>
                        <option value="ttd_surat">Penanda Tangan</option>
                        <option value="tanggal">Tanggal</option>
                        <option value="penerima_surat">Tanggal</option>
                    </select>
                    <button onclick="saveAnnotation()"
                            class="px-4 py-2 bg-[#6C88A4] text-white rounded hover:bg-[#2C3E50] hover:text-white">
                        Simpan
                    </button>
                    <button onclick="closeAnnotationModal()"
                            class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-600">
                        Batal
                    </button>
                </div>
            </div>
        </template>

        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>
    <x-ocr-help-modal />

    @push('scripts')
        <script>
            console.log('--- Script loaded! ---');
            let currentSelection = null; // Menyimpan seleksi teks saat ini
    
            const typeColors = {
                nomor_surat: "#ffeb3b",
                isi_surat: "#4caf50",
                ttd_surat: "#2196f3",
                tanggal: "#f57c00",
                penerima_surat: "#a578f5"
            };
    
            let textNodes = []; 
            let plainTextContent = '';
            let ocrContentDiv = null; // Akan diinisialisasi di DOMContentLoaded

            // Fungsi utilitas untuk meng-escape karakter regex
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }
    
            // Fungsi untuk mendapatkan posisi awal dan panjang seleksi secara global
            function getSelectionGlobalOffsets(globalStart, selectedText, container, cleanPlainText) {
                
                console.log('[getSelectionGlobalOffsets] Global Start:', globalStart);
                const globalLength = selectedText.length;
    
                const expectedText = cleanPlainText.substring(globalStart, globalStart + globalLength);

                if (!selectedText || !container || !cleanPlainText) return null;

                if (expectedText === selectedText) {
                    return { start: globalStart, length: globalLength };
                } else {
                    console.warn(`[getSelectionGlobalOffsets] Verification failed: Expected "${expectedText}", got "${selectedText}". Attempting fallback.`);
                    // Fallback: Cari posisi teks yang dipilih di plainTextContent
                    const fallbackStart = cleanPlainText.indexOf(selectedText, Math.max(0, globalStart - 50)); // Cari di sekitar posisi yang diharapkan
                    if (fallbackStart !== -1) {
                         console.warn(`[getSelectionGlobalOffsets] Fallback successful. Found at ${fallbackStart}.`);
                         return { start: fallbackStart, length: selectedText.length };
                    }
                    console.error("[getSelectionGlobalOffsets] Fallback failed: Selected text not found in plainTextContent.");
                    return null;
                }
                currentSelection = null;
            }
    
            // Inisialisasi/inisialisasi ulang textNodes dan plainTextContent
            // Parameter initialOcrHtml sekarang opsional, diambil dari Alpine jika tidak diberikan.
            function initTextNodes(initialOcrHtml = null) {
                if (!ocrContentDiv) {
                    console.error("[initTextNodes] ocrContentDiv is not set.");
                    return;
                }
    
                // Langkah 1: Reset DOM ocrContentDiv ke konten OCR MURNI
                // Ambil dari parameter atau dari Alpine Scope
                const alpineDataScope = Alpine.$data(ocrContentDiv.closest('[x-data]'));
                const ocrToUse = initialOcrHtml !== null ? initialOcrHtml : alpineDataScope.ocr;
                
                // Pastikan ini benar-benar menghapus highlight sebelum mengisi HTML
                // Seharusnya ocrToUse sudah bersih dari tag <mark> dari watcher input Alpine
                ocrContentDiv.innerHTML = ocrToUse;
                
                // Langkah 2: Hitung plainTextContent dari DOM yang sudah bersih
                plainTextContent = (ocrContentDiv.textContent || ocrContentDiv.innerText || "").replace(/\r\n|\r/g, '\n'); // Normalize newlines
    
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
    
            // Fungsi untuk membungkus teks dengan tag <mark> pada posisi tertentu
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

                // --- Logika yang Lebih Robust untuk Mencari Range ---
                // Kita perlu menemukan titik awal dan akhir Range dalam struktur DOM asli,
                // bukan hanya dari peta textNodes yang mungkin memiliki "celah".

                let charIndex = 0; // Melacak indeks karakter absolut di DOM
                let rangeStartFound = false;
                let rangeEndFound = false;
                let rangeToHighlight = document.createRange(); // Range yang akan kita gunakan

                // Mendapatkan semua node di dalam ocrContentDiv (termasuk non-teks)
                const walker = document.createTreeWalker(
                    ocrContentDiv,
                    NodeFilter.SHOW_ALL, // SHOW_ALL untuk melihat semua node, bukan hanya TEXT
                    null
                );

                let currentNode;
                while ((currentNode = walker.nextNode())) {
                    if (currentNode.nodeType === Node.TEXT_NODE) {
                        const nodeLength = currentNode.nodeValue.length;

                        // Cek jika start highlight berada di node ini
                        if (!rangeStartFound && start >= charIndex && start <= (charIndex + nodeLength)) {
                            rangeToHighlight.setStart(currentNode, start - charIndex);
                            rangeStartFound = true;
                        }

                        // Cek jika end highlight berada di node ini
                        if (rangeStartFound && (start + length) >= charIndex && (start + length) <= (charIndex + nodeLength)) {
                            rangeToHighlight.setEnd(currentNode, (start + length) - charIndex);
                            rangeEndFound = true;
                        }
                        
                        charIndex += nodeLength; // Tambahkan panjang teks ke indeks karakter
                    } else if (currentNode.nodeType === Node.ELEMENT_NODE) {
                        // Tangani elemen yang mungkin memengaruhi offset teks, seperti <br> atau spasi tambahan
                        // Ini bisa menjadi sumber utama inkonsistensi antara plainTextContent dan DOM visual
                        if (currentNode.tagName === 'BR') {
                            charIndex += 1; // Untuk <br>, anggap itu sebagai 1 karakter newline di plainTextContent
                        } else if (currentNode.tagName === 'P' || currentNode.tagName === 'DIV') {
                            // Untuk tag block, bisa jadi ada 1 atau 2 newlines yang tidak ada di textContent
                            // Ini sangat tergantung pada bagaimana plainTextContent dihitung.
                            // Jika plainTextContent dibuat dari textContent, maka ini mungkin sudah tertangani.
                        }
                        // Penting: Jika ada elemen yang menyisipkan spasi atau karakter, itu harus disinkronkan dengan plainTextContent
                    }

                    if (rangeStartFound && rangeEndFound) {
                        break; // Kedua titik ditemukan, keluar dari loop
                    }
                }

                if (!rangeStartFound || !rangeEndFound) {
                    console.warn(`[wrapTextByPosition] FINAL FAIL: Could not determine full range for Text: "${textToHighlight}", start: ${start}, length: ${length}. Range might span complex DOM.`);
                    return; // Tidak dapat menemukan titik awal atau akhir yang valid dalam DOM
                }

                try {
                    // --- Optional Guard (Jika Anda ingin mencegah penumpukan visual) ---
                    // Namun, jika initTextNodes() sudah memastikan DOM selalu bersih, ini tidak diperlukan.
                    // Jika Anda tetap melihat masalah tumpukan, ini bisa menjadi penyebab.
                    if (rangeToHighlight.commonAncestorContainer.closest && rangeToHighlight.commonAncestorContainer.closest('mark')) {
                        console.warn("[wrapTextByPosition] Skipping highlight: part already marked or inside an existing mark (this should not happen if DOM is perfectly clean).");
                        // return; // Hapus return jika Anda ingin tetap memaksa highlight (dan mungkin menimpa)
                    }
                    // --- End Optional Guard ---

                    const mark = document.createElement("mark");
                    mark.setAttribute("data-type", type);
                    mark.style.backgroundColor = typeColors[type] || "#ccc"; 

                    rangeToHighlight.surroundContents(mark); 

                    console.log(`[wrapTextByPosition] Wrapped "${textToHighlight}" (type: ${type}) at start ${start}.`);
                } catch (e) {
                    console.error(`[wrapTextByPosition] Error wrapping text "${textToHighlight}" (type: ${type}) at start ${start}:`, e);
                    console.error("DOM Exception Name:", e.name, "Message:", e.message);
                    console.error("Range info at error:", { 
                        startNode: rangeToHighlight.startContainer, 
                        startOffset: rangeToHighlight.startOffset, 
                        endNode: rangeToHighlight.endContainer, 
                        endOffset: rangeToHighlight.endOffset 
                    });
                }
            }
    
            // Fungsi untuk merender semua highlight dari data annotations
            function renderHighlights() {
                if (!ocrContentDiv) {
                    console.error("[renderHighlights] ocrContentDiv not set. Skipping.");
                    return;
                }
    
                // 1. Dapatkan konten OCR asli dari Alpine Scope (yang seharusnya sudah bersih dari mark)
                const alpineDataScope = Alpine.$data(ocrContentDiv.closest('[x-data]'));
                const originalOcrHtml = alpineDataScope.ocr; // Ini adalah sumber kebenaran HTML yang bersih
    
                // 2. SELALU bersihkan DOM dan bangun ulang textNodes dari konten OCR asli
                initTextNodes(originalOcrHtml); 
    
                const annotationsObject = alpineDataScope.annotations || {};
                console.log('[renderHighlights] Annotations to render:', annotationsObject);
    
                // 3. Ubah annotations menjadi array dan urutkan berdasarkan posisi 'start' secara DESCENDING
                const sortedAnnotations = Object.entries(annotationsObject)
                    .map(([key, data]) => ({ key, ...data }))
                    .filter(a => typeof a.start === 'number' && typeof a.length === 'number' && typeof a.text === 'string' && a.text.length > 0)
                    .sort((a, b) => b.start - a.start); // Urutkan dari akhir ke awal untuk menghindari masalah offset
    
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
            function saveAnnotation () {
                const selectedType = document.getElementById("type-dropdown").value;
                const selection = window.getSelection();
                const selectedText = selection.toString().trim().replace(/\r\n|\r/g, '\n'); // Normalize

                console.log('[saveAnnotation] Selection:', selection.anchorOffset);
    
                if (!selectedType || !selection || selection.rangeCount === 0 || selection.isCollapsed) {
                    alert("Silakan pilih jenis anotasi dan pastikan ada teks yang terpilih.");
                    closeAnnotationModal(); // Tutup modal jika ada masalah
                    return;
                }
                if (!ocrContentDiv) {
                    console.error("[saveAnnotation] ocrContentDiv not set. Cannot save.");
                    closeAnnotationModal(); // Tutup modal jika ada masalah
                    return;
                }
    
                // Ini memastikan perhitungan offset berdasarkan DOM yang bersih,
                // karena initTextNodes akan dipanggil lagi di renderHighlights setelah dispatchUpdate.
                // Namun, untuk akurasi getSelectionGlobalOffsets saat ini, panggil initTextNodes.
                // initTextNodes() tanpa parameter akan membaca alpineDataScope.ocr yang diharapkan bersih.
                initTextNodes();
    
                const globalOffsets = getSelectionGlobalOffsets(selection.anchorOffset, selectedText, ocrContentDiv, plainTextContent);
                // const globalOffsets = getSelectionGlobalOffsets(selection, ocrContentDiv, plainTextContent);

                console.log('[saveAnnotation] Global Offsets:', globalOffsets);
    
                if (!globalOffsets) {
                    alert("Gagal mendapatkan posisi teks yang dipilih. Coba lagi.");
                    closeAnnotationModal();
                    return;
                }
    
                const { start: startIndex, length: selectedLength } = globalOffsets;
    
                if (!selectedText) {
                    console.log('Teks yang dipilih kosong.');
                    closeAnnotationModal();
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
    
                // Dispatch update ke Livewire. Alpine watcher untuk 'annotations' akan memicu renderHighlights.
                // 'ocrContentDiv.textContent' ini sudah bersih karena initTextNodes dipanggil di awal saveAnnotation.
                alpineDataScope.dispatchUpdate(
                    ocrContentDiv.textContent, // Menggunakan textContent yang bersih, bukan innerHTML
                    updatedAnnotationsObject
                );
    
                closeAnnotationModal();
                // currentSelection = null; // Reset seleksi
            };

            function closeAnnotationModal() {
                document.querySelectorAll(".modal-overlay").forEach(el => el.remove());
            }
    
            // Fungsi untuk menampilkan modal pemilihan jenis anotasi
            window.showModalAtPosition = function(range) {
                document.querySelectorAll(".modal-overlay").forEach(el => el.remove());
    
                const modalOverlay = document.createElement("div");
                modalOverlay.className = "modal-overlay fixed inset-0 z-[9999] bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center animate-fade-in-down";
    
                const template = document.getElementById("annotation-modal");
                if (!template) {
                    console.error("Annotation modal template not found.");
                    return;
                }
                const modalContent = template.content.cloneNode(true);
                modalOverlay.appendChild(modalContent);
                document.body.appendChild(modalOverlay);
    
                // Tambahkan event listener untuk tombol Batal di modal
                const cancelButton = modalOverlay.querySelector('button[onclick="closeAnnotationModal()"]');
                if (cancelButton) {
                    cancelButton.addEventListener('click', closeAnnotationModal);
                }

                // Tutup modal ketika overlay diklik
                modalOverlay.addEventListener("click", (e) => {
                    if (e.target === modalOverlay) {
                        closeAnnotationModal();
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
    
                // initTextNodes akan dipanggil oleh Alpine init() atau watcher 'ocr'
                // jadi tidak perlu panggil di sini lagi
                // initTextNodes(); 
    
                ocrContentDiv.addEventListener("mouseup", () => {
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0 && !selection.isCollapsed) {
                        const range = selection.getRangeAt(0);
                        console.log("Selection Range:", range.startContainer, range.startOffset, range.endContainer, range.endOffset);
                        const parentMark = range.commonAncestorContainer.closest?.("mark");
    
                        // Kita tetap tampilkan modal, tapi beri peringatan jika ada highlight
                        if (parentMark) {
                            console.warn("Seleksi sebagian atau seluruhnya berada di dalam highlight yang sudah ada. Akan tetap menampilkan modal.");
                            // Anda bisa tambahkan alert di sini jika perlu: alert("Seleksi Anda tumpang tindih dengan highlight yang sudah ada.");
                        }
    
                        currentSelection = {
                            text: selection.toString(),
                            range: range
                        };
    
                        showModalAtPosition(range);
                    }
                });

                // Event listener untuk menghapus highlight saat diklik
                // ocrContentDiv.addEventListener('click', (event) => {
                //     if (event.target.tagName === 'MARK') {
                //         const mark = event.target;
                //         const dataType = mark.getAttribute('data-type');
                //         if (confirm(`Hapus highlight untuk "${dataType}"?`)) {
                //             const alpineDataScope = Alpine.$data(ocrContentDiv.closest('[x-data]'));
                //             let updatedAnnotationsObject = { ...alpineDataScope.annotations };
                //             delete updatedAnnotationsObject[dataType];

                //             console.log(`[Click MARK] Deleting annotation for type: ${dataType}. Updated annotations:`, updatedAnnotationsObject);
                            
                //             // Perbarui annotations di Alpine, watcher akan memicu renderHighlights
                //             alpineDataScope.annotations = updatedAnnotationsObject;

                //             // Dispatch update ke Livewire
                //             alpineDataScope.dispatchUpdate(
                //                 ocrContentDiv.textContent, // Menggunakan textContent yang bersih
                //                 updatedAnnotationsObject
                //             );
                //         }
                //     }
                // });
            });
        </script>
    @endpush
</x-filament-panels::page>