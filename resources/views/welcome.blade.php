<!DOCTYPE html>
<html>

<head>
    <title>Image Upload/Capture</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .preview {
            display: block;
            width: 100%;
            max-width: 300px;
            margin-top: 20px;
        }

        #previewsContainer {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container">
        <h1 class="text-center my-4">Upload or Capture Images</h1>

        <button id="showModalButton" class="btn btn-primary d-none" onclick="showModal()">Upload or Capture Image</button>

        <!-- Modal -->
        <div id="uploadModal" class="modal fade" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-between">
                        <h5 class="modal-title" id="uploadModalLabel">Choose Upload Method</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Please choose a method to upload images:</p>
                        <button type="button" class="btn btn-primary me-2" onclick="showUpload()">Upload File</button>
                        <button type="button" class="btn btn-success" onclick="showCapture()">Capture Image</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container for capturing images -->
        <div id="captureContainer" class="d-none">
            <form action="{{ route('scan') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <input type="file" class="form-control" id="captureInput" name="images[]" accept="image/*"
                        capture="environment" multiple onchange="previewImages(event, 'previewsCapture')">
                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary" onclick="triggerCapture()">Take Another
                            Photo</button>
                        <button type="submit" class="btn btn-primary">Upload Captured Images</button>
                    </div>
                </div>
            </form>
            <div id="previewsCapture" class="mt-3"></div>
        </div>

        <!-- Container for uploading images -->
        <div id="uploadContainer">
            <form action="{{ route('scan') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <input type="file" class="form-control" name="images[]" accept="image/*" multiple
                        onchange="previewImages(event, 'previewsUpload')">
                    <button type="submit" class="btn btn-primary mt-3">Upload Files</button>
                </div>
            </form>
            <div id="previewsUpload" class="mt-3"></div>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 1024) {
                document.getElementById('showModalButton').classList.remove('d-none');
                document.getElementById('captureContainer').classList.add('d-none');
                document.getElementById('uploadContainer').classList.add('d-none');
            }
        });

        function previewImages(event, containerId) {
            const files = event.target.files;
            const previewsContainer = document.getElementById(containerId);
            previewsContainer.innerHTML = ''; // Clear previous previews

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgContainer = document.createElement('div');
                    imgContainer.className = 'preview-container';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview img-fluid rounded';
                    img.alt = 'Preview';

                    imgContainer.appendChild(img);
                    previewsContainer.appendChild(imgContainer);
                };
                reader.readAsDataURL(file);
            });
        }

        function triggerCapture() {
            document.getElementById('captureInput').click();
        }

        function showModal() {
            const modal = new bootstrap.Modal(document.getElementById('uploadModal'));
            modal.show();
        }

        function closeModal() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
            if (modal) {
                modal.hide();
            }
        }

        function showUpload() {
            document.getElementById('uploadContainer').classList.remove('d-none');
            document.getElementById('captureContainer').classList.add('d-none');
            closeModal();
            document.getElementById('showModalButton').classList.add('d-none');
        }

        function showCapture() {
            document.getElementById('captureContainer').classList.remove('d-none');
            document.getElementById('uploadContainer').classList.add('d-none');
            closeModal();
            document.getElementById('showModalButton').classList.add('d-none');
        }
    </script>
</body>

</html>
