<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuratTEDI - Pengajuan dan Manajemen Surat Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F3F4F6; /* Menggunakan warna terang dari tema Filament */
        }
        .hero-background {
            /* Ganti dengan gambar latar belakang Anda, atau buat gradien */
            background-image: url('https://via.placeholder.com/1500x800/5a5aa3/FFFFFF?text=SuratTEDI+Background'); 
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .hero-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(90, 90, 163, 0.7); 
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .btn-login-custom {
            background-color: #424275; 
            color: #F9FAFB; 
            transition: background-color 0.3s, color 0.3s;
        }
        .btn-login-custom:hover {
            background-color: #5a5aa3; 
        }
        .btn-ajukan {
            background-color: #424275;
        }
        .btn-ajukan:hover {
            background-color: #5a5aa3 ;
        }
    </style>
</head>
<body class="antialiased text-gray-800">

    <header class="bg-white shadow-md sticky top-0 z-10">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="#" class="text-xl font-bold" style="color: #424275">SuratTEDI</a> 
            <div>
                {{-- <a href="{{ route('filament.admin.auth.login') }}" class="px-4 py-2 rounded-md text-[#5a5aa3] hover:bg-[#bcbcd8] transition duration-300">Login Admin</a> --}}
                <a href="{{ route('filament.user.auth.login') }}" class="px-4 py-2 rounded-md btn-login-custom ml-4">Login</a>
            </div>
        </nav>
    </header>

    <section class="hero-background text-white py-20 md:py-32">
        <div class="container mx-auto px-6 text-center hero-content">
            <h3 class="text-xl md:text-3xl font-extrabold leading-tight mb-4 animate-fade-in-down">
                Ajukan Surat, Semudah Satu Kali Klik!
            </h3>
            <p class="text-md md:text-lg mb-8 opacity-90 animate-fade-in-up">
                SuratTEDI: Solusi Digital untuk Pengajuan dan Manajemen Surat di Lingkungan Departemen Teknik Elektro dan Informatika Universitas Gadjah Mada.
            </p>
            <div class="space-x-4 animate-fade-in-up">
                <a href="{{ route('filament.user.auth.login') }}" class="inline-block btn-ajukan text-white font-bold py-3 px-8 rounded-full transition duration-300 text-md shadow-lg">
                    Mulai Ajukan Surat
                </a>
                <a href="#features" class="inline-block bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-full hover:bg-white hover:text-black transition duration-300 text-md shadow-lg">
                    Pelajari Lebih Lanjut
                </a>
            </div>
        </div>
    </section>

    <section id="features" class="py-16 bg-[#F9FAFB]"> <div class="container mx-auto px-6">
            <h3 class="text-xl md:text-2xl font-bold text-center mb-12 text-[#5a5aa3]">Mengapa Menggunakan SuratTEDI?</h3> <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gray-50 p-8 rounded-lg shadow-md text-center hover:shadow-xl transition-shadow duration-300">
                    <div class="text-[#5a5aa3] mb-4"> <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h4 class="text-lg font-semibold mb-3 text-gray-900">Proses Cepat & Mudah</h4>
                    <p class="text-gray-700">Ajukan permohonan surat kapan saja dan di mana saja. Tidak perlu antre, tidak perlu cetak form.</p>
                </div>
                <div class="bg-gray-50 p-8 rounded-lg shadow-md text-center hover:shadow-xl transition-shadow duration-300">
                    <div class="text-[#5a5aa3] mb-4"> <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </div>
                    <h4 class="text-lg font-semibold mb-3 text-gray-900">Transparansi Status</h4>
                    <p class="text-gray-700">Lacak status pengajuan surat Anda secara <i>real-time</i> dari awal hingga surat siap diambil/diunduh.</p>
                </div>
                <div class="bg-gray-50 p-8 rounded-lg shadow-md text-center hover:shadow-xl transition-shadow duration-300">
                    <div class="text-[#5a5aa3] mb-4"> <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h4 class="text-lg font-semibold mb-3 text-gray-900">Efisiensi Admin</h4>
                    <p class="text-gray-700">Mempermudah admin dalam memproses, menandatangani, dan mengelola seluruh dokumen surat keluar.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-6 text-center">
            <p class="mb-4">&copy; 2025 SuratTEDI. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>