<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="{{ public_path('css/template/style.css') }}">
</head>
<body class="doc-root">
  <x-header-layout/>
  <div class="doc-body">
    {{ $slot }}
  </div>
</body>
</html>