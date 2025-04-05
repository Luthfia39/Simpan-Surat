<div>
    <div class="row justify-content-center">
        <div class="card-body">

            <form wire:submit.prevent="scan">
                @csrf

                <div id="dropZone" class="upload-zone rounded-3 p-4"
                    onclick="document.getElementById('fileInput').click()">
                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                        <div class="upload-icon mb-3 text-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor"
                                class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
                                <path
                                    d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z" />
                            </svg>
                        </div>
                        <p class="upload-message text-muted mb-0">
                            Klik atau seret file PDF di sini
                        </p>

                        <!-- Input File -->
                        <input wire:model.defer="files" type="file" id="fileInput" class="d-none" accept=".pdf">

                        <!-- Preview Nama File (Tampilkan di #preview) -->
                        <div id="preview" class="mt-3">
                            @if ($fileName)
                                <span class="badge bg-success p-2">
                                    📄 {{ $fileName }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div id="errorAlert" class="alert alert-danger mt-4 mb-0 d-none">
                    <ul class="mb-0" id="errorList"></ul>
                </div>

                <div class="text-center mt-4">
                    <x-button type="submit" variant="primary" width="full">Unggah</x-button>
                </div>
            </form>
        </div>
    </div>
</div>
