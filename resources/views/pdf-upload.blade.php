<!DOCTYPE html>
<html>

<head>
    <title>PDF to Image Converter</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <form action="{{ url('convert-pdf') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="pdf_file" accept="application/pdf" required>
        <button type="submit">Convert PDF</button>
    </form>
</body>

</html>
