<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    {{-- <link rel="shortcut icon" href="{{asset('assets/img/icons/icon-48x48.png')}}" /> --}}

    <title>@yield('title')</title>

    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    {{-- <script src="{{ asset('https://cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script> --}}

    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @yield('styles')
    @livewireStyles
</head>

<body>
    <div class="wrapper">
        @include('layouts.sidebar')
        <div class="main">
            @include('layouts.navbar')
            @yield('content')
            @include('layouts.footer')
        </div>

    </div>

    @livewireScripts

    <script src="{{ asset('https://cdn.jsdelivr.net/npm/sweetalert2@11') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    @yield('customJs')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>

</html>
