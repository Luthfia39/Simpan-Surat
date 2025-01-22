<!DOCTYPE html>
<html>

<head>
    <title>Image Upload/Capture</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

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

        <button id="showModalButton" class="btn btn-primary d-none" onclick="showModal()">Upload or Capture
            Image</button>

        <!-- Modal -->
        <div id="uploadModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-between">
                        <h5 class="modal-title" id="uploadModalLabel">Choose Upload Method</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Please choose a method to upload images:</p>
                        <button type="button" class="btn btn-primary mr-2" onclick="showUpload()">Upload File</button>
                        <button type="button" class="btn btn-success" onclick="showCapture()">Capture Image</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container for capturing images on mobile/tablet -->
        <div id="captureContainer" class="d-none">
            <form action="{{ route('scan') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="file" id="captureInput" name="images[]" accept="image/*" capture="environment" multiple
                    onchange="previewImages(event, true)">
                <button type="button" class="btn btn-secondary mt-3" onclick="addMoreCaptures()">Capture Image</button>
                <button type="submit" class="btn btn-primary mt-3">Upload</button>
            </form>
            <div id="previewsCapture" class="mt-3"></div>
        </div>

        <!-- Container for uploading images on desktop -->
        <div id="uploadContainer">
            <form action="{{ route('scan') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="file" name="images[]" accept="image/*" multiple onchange="previewImages(event, false)">
                <button type="submit" class="btn btn-primary mt-3">Upload</button>
            </form>
            <div id="previewsUpload" class="mt-3"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 1024) {
                document.getElementById('showModalButton').classList.remove('d-none');
                document.getElementById('captureContainer').classList.add('d-none');
                document.getElementById('uploadContainer').classList.add('d-none');
            }
        });

        function previewImages(event, upload) {
            const files = event.target.files;
            const previewsContainerUpload = document.getElementById('previewsUpload');
            const previewsContainerCapture = document.getElementById('previewsCapture');
            previewsContainerUpload.innerHTML = ''; // Clear previous previews
            // previewsContainerCapture.innerHTML = ''; // Clear previous previews

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview';
                    if (upload) {
                        previewsContainerCapture.appendChild(img);
                    }
                    if (!upload) {
                        previewsContainerUpload.appendChild(img);
                    }

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

        function addMoreCaptures() {
            const captureInput = document.getElementById('captureInput');
            captureInput.disabled = false;
            captureInput.click();
            captureInput.disabled = true;
        }
    </script>
</body>

</html>
