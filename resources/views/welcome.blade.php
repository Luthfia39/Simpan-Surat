<!DOCTYPE html>
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
</html>
