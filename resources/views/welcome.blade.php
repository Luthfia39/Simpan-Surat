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

<!-- resources/views/documents/upload.blade.php -->
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
            width: 100px;
            height: 100px;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-cloud-arrow-up" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M7.646 5.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708l2-2z"/>
                                            <path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383z"/>
                                        </svg>
                                    </div>
                                    <p class="upload-message text-muted mb-0">
                                        Unggah beberapa gambar (.png, .jpg, .jpeg) atau 1 file PDF
                                    </p>
                                    <input type="file" id="fileInput" name="files[]" class="d-none" multiple accept="image/*,.pdf">
                                    
                                    <div id="preview" class="d-flex flex-wrap gap-3 justify-content-center mt-4"></div>
                                </div>
                            </div>

                            <div id="errorAlert" class="alert alert-danger mt-4 mb-0 d-none">
                                <ul class="mb-0" id="errorList"></ul>
                            </div>

                            @if (session('result'))
                            <div class="alert alert-success mt-4 mb-0">
                                {{-- {{ session('result') }} --}}
                                <pre class="bg-light p-3 rounded">{{ print_r(session('result'), true) }}</pre>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const preview = document.getElementById('preview');
        const errorAlert = document.getElementById('errorAlert');
        const errorList = document.getElementById('errorList');
        let currentFiles = [];

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

        function validateFiles(files) {
            const errors = [];
            const imageFiles = [];
            const pdfFiles = [];
            
            // Categorize files
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    imageFiles.push(file);
                } else if (file.type === 'application/pdf') {
                    pdfFiles.push(file);
                } else {
                    errors.push(`File "${file.name}" bukan merupakan file gambar atau PDF yang valid`);
                }
            });

            // Validate PDF files
            if (pdfFiles.length > 1) {
                errors.push('Hanya 1 file PDF yang diperbolehkan');
                return { valid: false, errors };
            }

            // If there's a PDF, no images allowed and vice versa
            if (pdfFiles.length === 1 && imageFiles.length > 0) {
                errors.push('Tidak dapat mengunggah gambar dan PDF secara bersamaan');
                return { valid: false, errors };
            }

            return { 
                valid: errors.length === 0, 
                errors,
                files: pdfFiles.length ? pdfFiles : imageFiles 
            };
        }

        function handleFiles(e) {
            const validation = validateFiles(e.target.files);
            
            // Clear previous errors
            errorList.innerHTML = '';
            errorAlert.classList.add('d-none');
            
            if (!validation.valid) {
                errorAlert.classList.remove('d-none');
                validation.errors.forEach(error => {
                    const li = document.createElement('li');
                    li.textContent = error;
                    errorList.appendChild(li);
                });
                return;
            }

            currentFiles = validation.files;
            preview.innerHTML = '';
            
            if (currentFiles.length > 0) {
                dropZone.classList.add('has-files');
            } else {
                dropZone.classList.remove('has-files');
            }
            
            currentFiles.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onloadend = function() {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        div.innerHTML = `
                            <img src="${reader.result}" class="rounded shadow-sm" alt="Preview">
                            <button type="button" class="remove-btn btn btn-danger btn-sm rounded-circle" 
                                onclick="event.stopPropagation(); removeFile('${file.name}');">×</button>
                        `;
                        preview.appendChild(div);
                    }
                } else if (file.type === 'application/pdf') {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <div class="w-100 h-100 rounded shadow-sm bg-light d-flex align-items-center justify-content-center">
                            <span class="text-muted small">PDF</span>
                        </div>
                        <button type="button" class="remove-btn btn btn-danger btn-sm rounded-circle" 
                            onclick="event.stopPropagation(); removeFile('${file.name}');">×</button>
                    `;
                    preview.appendChild(div);
                }
            });
        }

        function removeFile(fileName) {
            currentFiles = currentFiles.filter(file => file.name !== fileName);
            
            // Create a new FileList-like object
            const dt = new DataTransfer();
            currentFiles.forEach(file => dt.items.add(file));
            fileInput.files = dt.files;
            
            // Trigger handleFiles to update preview
            handleFiles({ target: { files: dt.files } });
        }

        function checkFiles() {
            if (preview.children.length === 0) {
                dropZone.classList.remove('has-files');
            }
        }
    </script>
</body>
</html>