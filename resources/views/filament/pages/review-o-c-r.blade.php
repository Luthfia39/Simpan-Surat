<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->heading }}
        </x-slot>

        <x-slot name="description">
            Cek kembali hasil OCR berikut ini, pastikan data yang disimpan telah sesuai.
        </x-slot>

        <div>
            {{-- Overview of All Documents Found (unchanged) --}}
            <h2 class="text-xl font-bold mb-4">Dokumen Ditemukan (Total: {{ $foundLetters->count() }}):</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                @foreach($foundLetters as $doc)
                    <div class="border p-4 rounded-lg shadow-sm bg-white">
                        <p class="font-semibold text-lg">Dokumen #{{ $doc->document_index }}</p>
                        <p class="text-sm text-gray-600">Jenis: {{ $doc->letter_type ?? 'Tidak Diketahui' }}</p>
                        <p class="text-sm text-gray-600">ID Database: {{ $doc->id }}</p>
                        @if ($doc->document_index === $selectedDocumentIndexForViewer)
                            <span class="text-xs text-blue-500 font-semibold"> (Sedang di Viewer)</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <hr class="my-6">

            <h3 class="text-lg font-semibold mb-2">Pilih Dokumen untuk Pratinjau OCR & Edit Form:</h3>
            <x-filament::input.wrapper class="mb-4">
                <x-filament::input.select
                    wire:model.live="selectedDocumentIndexForViewer"
                    x-on:change="
                        $wire.loadDocumentForViewer($event.target.value)
                    "
                >
                    <option value="">-- Pilih Dokumen --</option>
                    @foreach ($foundLetters as $letter)
                        <option value="{{ $letter->document_index }}">
                            Dokumen {{ $letter->document_index }} ({{ $letter->letter_type ?? 'Tidak Diketahui' }})
                        </option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>

            {{-- Display the single editable form, bound to the currently selected document --}}
            <h3 class="text-lg font-semibold mb-2">Formulir Edit untuk Dokumen yang Dipilih:</h3>
            @php
                $selectedDocument = $foundLetters->firstWhere('document_index', $selectedDocumentIndexForViewer);
            @endphp

            @if ($selectedDocument)
                <x-filament::section class="mb-6">
                    <x-slot name="heading">
                        Edit Dokumen #{{ $selectedDocument->document_index }}
                        <span class="text-sm text-gray-500 ml-2">({{ $selectedDocument->letter_type ?? 'Tidak Diketahui' }})</span>
                    </x-slot>

                    <x-filament-panels::form
                        wire:submit.prevent="saveAllDocuments"
                        wire:model="documentsData.{{ $selectedDocument->document_index }}"
                    >
                        @foreach ($this->form as $component)
                            {{ $component }}
                        @endforeach
                    </x-filament-panels::form>

                    {{-- Display extracted fields below the form (read-only) --}}
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @php
                            $extractedFields = is_string($selectedDocument->extracted_fields) ? json_decode($selectedDocument->extracted_fields, true) : ($selectedDocument->extracted_fields ?? []);
                        @endphp
                        @if(!empty($extractedFields))
                            <h3 class="text-md font-semibold col-span-full">Bidang yang Terdeteksi Otomatis:</h3>
                            @foreach($extractedFields as $key => $values)
                                <x-filament::input.wrapper>
                                    <x-slot name="label">{{ Str::headline($key) }}</x-slot>
                                    <x-filament::input
                                        type="text"
                                        value="{{ implode(', ', (array) $values) }}"
                                        disabled
                                        class="bg-gray-50 text-gray-700"
                                    />
                                </x-filament::input.wrapper>
                            @endforeach
                        @else
                            <p class="col-span-full text-gray-500">Tidak ada bidang terdeteksi secara otomatis.</p>
                        @endif
                    </div>
                </x-filament::section>
            @else
                <x-filament::card class="text-center text-gray-500">
                    <p>Pilih dokumen dari dropdown di atas untuk melihat detail dan mengeditnya.</p>
                </x-filament::card>
            @endif


            <h3 class="text-lg font-semibold mb-2 mt-6">Pratinjau Teks OCR & Anotasi:</h3>
            <div
                x-data="{
                    ocr: @entangle('ocr'),
                    annotations: @entangle('annotations'),
                    currentDocumentIndexInViewer: @entangle('selectedDocumentIndexForViewer'),
                    dispatchUpdate: function(finalOcr, finalAnnotations) {
                        $wire.updateDocumentOcrAndAnnotations(
                            this.currentDocumentIndexInViewer,
                            finalOcr,
                            finalAnnotations
                        );
                    },
                    init() {
                        // Watch for changes to the 'ocr' property.
                        // This ensures renderHighlights is called whenever new OCR text is loaded.
                        this.$watch('ocr', (newOcr) => {
                            if (newOcr) {
                                // Ensure DOM is updated by Alpine before running highlight logic
                                this.$nextTick(() => {
                                    renderHighlights();
                                });
                            }
                        });

                        this.$wire.on('ocr-loaded', ({ ocr, extracted_fields }) => {
                            this.annotations = extracted_fields; 
                            console.log('Livewire ocr-loaded event received.');
                        });
                    }
                }"
                class="border border-gray-300 p-4 rounded-lg bg-white overflow-auto max-h-96"
            >
                <div
                    id="ocr-content"
                    contenteditable="true"
                    class="p-4 min-h-[200px] border rounded bg-gray-50 mb-6 whitespace-pre-wrap"
                    x-html="ocr" {{-- Alpine's x-html handles the content --}}
                ></div>

                <input type="hidden" id="annotations-input" x-model="annotations" />

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
            </div>

            <div class="mt-6 flex justify-end">
                <x-filament-panels::form.actions :actions="$this->getFormActions()" />
            </div>
        </div>
    </x-filament::section>

    @push('scripts')
        <script>
            let currentSelection = null;
            let typeColors = {
                nomor_surat: "#ffeb3b",     // kuning
                isi_surat: "#4caf50",       // hijau
                penanda_tangan: "#2196f3",  // biru
                tanggal: "#f57c00"          // oranye
            };

            // renderHighlights now relies on global Alpine data
            function renderHighlights() {
                const container = document.getElementById("ocr-content");
                // Clear existing highlights (marks)
                container.querySelectorAll("mark").forEach(mark => {
                    const textNode = document.createTextNode(mark.textContent);
                    mark.parentNode.replaceChild(textNode, mark);
                });

                // Get the current OCR text (plain) and annotations from Alpine data
                // This assumes `renderHighlights` is called within an Alpine scope or can access it.
                // We'll get it from the `ocr-content` div's closest x-data context.
                const alpineDataScope = Alpine.$data(container.closest('[x-data]'));
                const ocr_text_plain = alpineDataScope.ocr;
                const annotations = alpineDataScope.annotations || [];

                // Re-render the plain OCR text first (x-html should have done this, but a safeguard)
                container.innerHTML = ocr_text_plain;

                // Re-initialize textNodes *after* innerHTML has been set
                initTextNodes();

                // Loop through annotations and apply highlights
                annotations.forEach(annotation => {
                    const key = Object.keys(annotation)[0];
                    const value = annotation[key];

                    if (!value || typeof value !== 'string') return;

                    const matches = findMatches(value, ocr_text_plain);

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
                console.log('Highlights re-rendered.');
            }

            let textNodes = [];
            function initTextNodes() {
                const container = document.getElementById("ocr-content");
                textNodes = [];
                let accumulatedLength = 0;

                // Filter out <mark> tags when building textNodes for accurate positioning
                const walker = document.createTreeWalker(
                    container,
                    NodeFilter.SHOW_TEXT,
                    { acceptNode: (node) => {
                        // Accept text nodes that are not inside a <mark> tag
                        return node.parentNode && node.parentNode.nodeName !== 'MARK' ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_SKIP;
                    }}
                );

                while (walker.nextNode()) {
                    const node = walker.currentNode;
                    textNodes.push({
                        node: node,
                        offset: accumulatedLength
                    });
                    accumulatedLength += node.nodeValue.length;
                }
                console.log('Text nodes initialized.');
            }

            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function findMatches(textToFind, fullOcrText) {
                const safeText = escapeRegExp(textToFind);
                // Ensure the regex searches the *plain text* of the OCR content
                const regex = new RegExp(safeText, 'gi');
                const matches = [];
                let match;
                while ((match = regex.exec(fullOcrText)) !== null) {
                    matches.push({
                        start: match.index,
                        length: match[0].length
                    });
                }
                return matches;
            }

            function wrapTextByPosition(container, textToHighlight, start, length, type) {
                let currentPos = 0;
                // This logic needs to correctly handle existing marks in the DOM if any.
                // It's safer to always render plain text then apply marks.
                // Our renderHighlights already does this, so this function is fine.
                for (const info of textNodes) {
                    const nodeStart = info.offset;
                    const nodeEnd = info.offset + info.node.nodeValue.length;

                    if (start >= nodeStart && start + length <= nodeEnd) {
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

            window.saveAnnotation = function() {
                const selectedType = document.getElementById("type-dropdown").value;
                const ocrContentDiv = document.getElementById("ocr-content");

                if (!selectedType || !currentSelection) {
                    alert("Silakan pilih jenis anotasi.");
                    return;
                }

                const { range } = currentSelection;
                const selectedText = range.toString().trim();

                if (!selectedText) return;

                const mark = document.createElement("mark");
                mark.setAttribute("data-type", selectedType);
                mark.style.backgroundColor = typeColors[selectedType];
                mark.textContent = selectedText;

                range.deleteContents();
                range.insertNode(mark);

                const alpineDataScope = Alpine.$data(ocrContentDiv.closest('[x-data]'));

                let updatedAnnotations = (alpineDataScope.annotations || []).filter(ann => !ann.hasOwnProperty(selectedType));
                updatedAnnotations.push({ [selectedType]: selectedText });

                // Dispatch update back to Livewire
                alpineDataScope.dispatchUpdate(
                    ocrContentDiv.textContent, // Get the current plain text (from the div)
                    updatedAnnotations
                );

                document.querySelectorAll(".modal-overlay").forEach(el => el.remove());
                currentSelection = null;
                console.log('Annotation saved and dispatched.');
            };

            window.showModalAtPosition = function(range) {
                document.querySelectorAll(".modal-overlay").forEach(el => el.remove());

                const modalOverlay = document.createElement("div");
                modalOverlay.className = "modal-overlay fixed z-50 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center";

                const template = document.getElementById("annotation-modal");
                const modalContent = template.content.cloneNode(true);
                modalOverlay.appendChild(modalContent);
                document.body.appendChild(modalOverlay);

                modalOverlay.addEventListener("click", (e) => {
                    if (e.target === modalOverlay) {
                        modalOverlay.remove();
                    }
                });
                console.log('Modal shown.');
            };

            document.addEventListener("DOMContentLoaded", () => {
                const editableDiv = document.getElementById("ocr-content");

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

                        window.showModalAtPosition(range);
                    }
                });
                console.log('DOMContentLoaded: Mouseup listener added.');
            });
        </script>
    @endpush
</x-filament-panels::page>