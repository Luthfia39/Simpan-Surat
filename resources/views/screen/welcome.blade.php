<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Manajemen Surat - Solusi terbaik untuk mengelola surat masuk dan keluar dengan fitur canggih dan mudah digunakan">
    <meta name="keywords" content="sistem manajemen surat, surat masuk, surat keluar, OCR, PDF, MongoDB, jurusan A, jurusan B, jurusan C, jurusan D">
    <title>Sistem Manajemen Surat | Solusi Tepat untuk Mengelola Dokumen Anda</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Sistem Manajemen Surat</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <!-- <a class="nav-link" href="#fitur">Fitur</a> -->
                        <a href="{{route('dashboard')}}" class="btn btn-primary btn-lg">Masuk</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="#kontak">Kontak</a>
                    </li> -->
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero mt-5 py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 mb-4">Sistem Manajemen Surat Lengkap &amp; Efisien</h1>
                    <p class="lead mb-4">Kelola surat masuk dan keluar dengan mudah, didukung fitur canggih dan integrasi dengan MongoDB untuk penyimpanan data yang aman.</p>
                    <a href="#" class="btn btn-primary btn-lg">Mulai Sekarang</a>
                </div>
                <div class="col-lg-6">
                    <center><img src="https://cdn-icons-png.flaticon.com/512/1067/1067555.png" alt="Sistem Manajemen Surat" class="img-fluid rounded" width="350"></center>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur Section -->
    <section id="fitur" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Fitur Unggulan</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <img src="https://cdn-icons-png.flaticon.com/512/1534/1534959.png" alt="Upload Surat" class="img-fluid mb-3" style="max-width: 100px;">
                            <h3 class="card-title mb-3">Upload Surat dengan OCR</h3>
                            <p class="card-text">Upload surat langsung dari perangkat Anda, didukung dengan teknologi OCR untuk mengkonversi gambar atau file PDF menjadi teks yang dapat diedit.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <img src="https://cdn-icons-png.flaticon.com/512/1534/1534963.png" alt="Generate PDF" class="img-fluid mb-3" style="max-width: 100px;">
                            <h3 class="card-title mb-3">Generate Surat dalam Format PDF</h3>
                            <p class="card-text">Buat dan generate surat keluar langsung dalam format PDF dengan template yang sudah disediakan, hemat waktu dan mudah digunakan.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <img src="https://cdn-icons-png.flaticon.com/512/1534/1534972.png" alt="Manajemen Surat" class="img-fluid mb-3" style="max-width: 100px;">
                            <h3 class="card-title mb-3">Manajemen Surat Masuk &amp; Keluar</h3>
                            <p class="card-text">Kelola surat masuk dan keluar dengan mudah, dilengkapi dengan fitur pencarian, kategori, dan label untuk memudahkan pengelolaan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kontak Section -->
    <!-- <section id="kontak" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Hubungi Kami</h2>
            <div class="row">
                <div class="col-md-6">
                    <form>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="pesan" class="form-label">Pesan</label>
                            <textarea class="form-control" id="pesan" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <h3>Informasi Kontak</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Example No. 123, Kota Example, Negara Example</p>
                    <p><i class="fas fa-phone"></i> +62 123 4567 890</p>
                    <p><i class="fas fa-envelope"></i> info@example.com</p>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2023 Sistem Manajemen Surat. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>