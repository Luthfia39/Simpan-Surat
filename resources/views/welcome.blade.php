{{-- <!DOCTYPE html>
<html>
<head>
    <title>File Upload</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <h1 class="text-center my-4">Upload File</h1>
        
        <form action="{{ route('scan') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="fileInput" class="form-label">Choose a file</label>
                <input type="file" class="form-control" id="fileInput" name="files[]" accept="image/*,.pdf" multiple onchange="validateFiles()">
                <p id="errorText" class="text-danger mt-2 d-none"></p>
                <button type="submit" class="btn btn-primary mt-3">Upload File</button>
            </div>
        </form>

        <div id="previewsContainer" class="mt-3"></div>

        <!-- Display Errors -->
        @if (session('error'))
            <p class="text-danger">{{ session('error') }}</p>
        @endif
        <!-- Display Results -->
        @if (session('result'))
            <h2 class="mt-4">Scan Results:</h2>
            <pre class="bg-light p-3 rounded">{{ print_r(session('result'), true) }}</pre>
        @endif
    </div>

    <script>
        function validateFiles() {
            const fileInput = document.getElementById('fileInput');
            const errorText = document.getElementById('errorText');
            const previewsContainer = document.getElementById('previewsContainer');
            const files = fileInput.files;
            errorText.classList.add('d-none');
            previewsContainer.innerHTML = ''; 
            
            let isPdf = false;
            let isImage = false;
            for (let file of files) {
                if (file.type === "application/pdf") {
                    isPdf = true;
                } else if (file.type.startsWith('image/')) {
                    isImage = true;
                }
            }
            
            if (isPdf && isImage) {
                errorText.textContent = "You cannot upload both images and PDFs at the same time.";
                errorText.classList.remove('d-none');
                fileInput.value = ""; 
                return;
            }
            
            if (isPdf && files.length > 1) {
                errorText.textContent = "Only one PDF file can be uploaded at a time.";
                errorText.classList.remove('d-none');
                fileInput.value = ""; 
                return;
            }
            
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail m-2';
                        img.style.maxWidth = '200px';
                        previewsContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
</body>
</html> --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggah Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .upload-zone {
            border: 2px dashed #dee2e6;
            cursor: pointer;
            min-height: 300px;
            transition: all 0.3s ease;
        }
        .upload-zone:hover {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.03);
        }
        .preview-item {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }
        .remove-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            z-index: 10;
            padding: 0;
            width: 20px;
            height: 20px;
            line-height: 18px;
            text-align: center;
        }
        .upload-icon {
            transition: all 0.3s ease;
        }
        .has-files .upload-message {
            display: none;
        }
        .has-files .upload-icon {
            transform: scale(0.8);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Unggah Dokumen</h2>
                        <p class="text-center text-muted mb-4">Unggah dokumen pilihan Anda untuk diproses!</p>

                        <form action="{{ route('scan') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div id="dropZone" class="upload-zone rounded-3 p-4" onclick="document.getElementById('fileInput').click()">
                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                    <div class="upload-icon mb-3 text-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
                                            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                            <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                                        </svg>
                                    </div>
                                    <p class="upload-message text-muted mb-0">
                                        Klik atau seret file PDF di sini
                                    </p>
                                    <input type="file" id="fileInput" name="files[]" class="d-none" accept=".pdf">
                                    
                                    <div id="preview" class="d-flex justify-content-center mt-4"></div>
                                </div>
                            </div>

                            <div id="errorAlert" class="alert alert-danger mt-4 mb-0 d-none">
                                <ul class="mb-0" id="errorList"></ul>
                            </div>

                            @if (session('results'))
                                <div class="alert alert-success mt-4 mb-0">
                                    <pre>{{ json_encode(session('results')) }}</pre>
                                    <p>--------------------------------------</p>
                                    <p>Nomor surat :</p>

                                    @if (session('no_letter'))
                                        @foreach (session('no_letter') as $key => $value) 
                                            <pre>{{ $value }}</pre>
                                        @endforeach
                                    @else
                                        <p>Tidak ada nomor surat ditemukan.</p>
                                    @endif
                                </div>
                            @endif

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary px-5">Unggah</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const preview = document.getElementById('preview');
        const errorAlert = document.getElementById('errorAlert');
        const errorList = document.getElementById('errorList');
        let currentFile = null;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        dropZone.addEventListener('drop', handleDrop, false);
        fileInput.addEventListener('change', handleFiles);

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            dropZone.classList.add('border-primary');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-primary');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles({ target: { files: files } });
        }

        function validateFile(file) {
            if (!file) return { valid: false, error: 'Pilih file PDF untuk diunggah' };
            if (file.type !== 'application/pdf') {
                return { valid: false, error: 'Hanya file PDF yang diperbolehkan' };
            }
            return { valid: true };
        }

        function handleFiles(e) {
            const file = e.target.files[0];
            const validation = validateFile(file);
            
            // Clear previous errors
            errorList.innerHTML = '';
            errorAlert.classList.add('d-none');
            
            if (!validation.valid) {
                errorAlert.classList.remove('d-none');
                const li = document.createElement('li');
                li.textContent = validation.error;
                errorList.appendChild(li);
                preview.innerHTML = '';
                dropZone.classList.remove('has-files');
                currentFile = null;
                return;
            }

            currentFile = file;
            preview.innerHTML = '';
            dropZone.classList.add('has-files');
            
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <div class="w-100 h-100 rounded shadow-sm bg-light d-flex flex-column align-items-center justify-content-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-file-pdf text-danger mb-2" viewBox="0 0 16 16">
                        <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                        <path d="M4.603 12.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.701 19.701 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.187-.012.395-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.065.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.716 5.716 0 0 1-.911-.95 11.642 11.642 0 0 0-1.997.406 11.311 11.311 0 0 1-1.021 1.51c-.29.35-.608.655-.926.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.27.27 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.647 12.647 0 0 1 1.01-.193 11.666 11.666 0 0 1-.51-.858 20.741 20.741 0 0 1-.5 1.05zm2.446.45c.15.162.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.881 3.881 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                    </svg>
                    <span class="text-muted small">${file.name}</span>
                </div>
                <button type="button" class="remove-btn btn btn-danger btn-sm rounded-circle" 
                    onclick="event.stopPropagation(); removeFile();">×</button>
            `;
            preview.appendChild(div);
        }

        function removeFile() {
            currentFile = null;
            fileInput.value = '';
            preview.innerHTML = '';
            dropZone.classList.remove('has-files');
        }
    </script>
</body>
</html>