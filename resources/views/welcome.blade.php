<!DOCTYPE html>
<html>

<head>
    <title>Ekstrak Dokumen</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .preview {
            display: block;
            width: 100%;
            max-width: 300px;
            margin-top: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container">
        <h1 class="text-center my-4">Scan Dokumen</h1>

        <button id="showModalButton" class="btn btn-primary d-none" onclick="showModal()">Unggah Gambar</button>

        <!-- Modal -->
        <div id="uploadModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">Pilih Metode Unggahan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Silakan pilih metode untuk mengunggah gambar:</p>
                        <button type="button" class="btn btn-primary mr-2" onclick="showUpload()">Unggah File</button>
                        <button type="button" class="btn btn-success" onclick="showCapture()">Ambil Gambar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container for capturing images on mobile/tablet -->
        <div id="captureContainer" class="d-none">
            <form action="/scan" method="post" enctype="multipart/form-data">
                @csrf
                <input type="file" name="images[]" accept="image/*" capture="environment" multiple
                    onchange="previewImages(event)">
                <button type="submit" class="btn btn-primary mt-3">Upload</button>
            </form>
            <div id="previews" class="mt-3"></div>
        </div>

        <!-- Container for uploading images on desktop -->
        <div id="uploadContainer">
            <form action="/scan" method="post" enctype="multipart/form-data">
                @csrf
                <input type="file" name="images[]" accept="image/*" multiple onchange="previewImages(event)">
                <button type="submit" class="btn btn-primary mt-3">Upload</button>
            </form>
            <div id="previews" class="mt-3"></div>
        </div>

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

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 1024) {
                document.getElementById('showModalButton').classList.remove('d-none');
                document.getElementById('captureContainer').classList.add('d-none');
                document.getElementById('uploadContainer').classList.add('d-none');
            }
        });

        function previewImages(event) {
            const files = event.target.files;
            const previewsContainer = document.getElementById('previews');
            previewsContainer.innerHTML = ''; // Clear previous previews

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview';
                    previewsContainer.appendChild(img);
                    img.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        }

        function closeModal() {
            $('#uploadModal').modal('hide');
        }

        function showModal() {
            $('#uploadModal').modal('show');
        }

        function showUpload() {
            document.getElementById('uploadContainer').classList.remove('d-none');
            closeModal();
            document.getElementById('showModalButton').classList.add('d-none');
        }

        function showCapture() {
            document.getElementById('captureContainer').classList.remove('d-none');
            closeModal();
            document.getElementById('showModalButton').classList.add('d-none');
        }
    </script>
</body>

</html>
