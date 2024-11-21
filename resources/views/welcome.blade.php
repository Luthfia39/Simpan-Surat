<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCR Scan Page</title>
</head>

<body>
    <h1>Upload Document for OCR</h1>

    <!-- Display Errors -->
    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <!-- Display Results -->
    @if (session('result'))
        <h2>Scan Results:</h2>
        <pre>{{ print_r(session('result'), true) }}</pre>
    @endif

    <!-- Upload Form -->
    <form action="{{ route('scan') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="document">Choose Document:</label>
        <input type="file" name="document" id="document" required>
        <button type="submit">Scan Document</button>
    </form>
</body>

</html>
