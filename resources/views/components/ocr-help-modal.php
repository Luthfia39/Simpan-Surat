<div id="ocr-help-modal" style="display: none;"
    class="fixed inset-0 z-[9999] bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center animate-fade-in-down overflow-y-auto">
    
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-auto my-auto transform transition-all scale-100 opacity-100"
         onclick="event.stopPropagation()">
        
        <div class="p-6 sm:p-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-extrabold text-gray-900">
                    Panduan Anotasi Teks OCR
                </h3>
                <button type="button" onclick="closeOcrHelpModal()"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-full p-1 transition ease-in-out duration-150">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="sr-only">Tutup panduan</span>
                </button>
            </div>

            <p class="text-base text-gray-700 mb-6 leading-relaxed">
                Ikuti langkah-langkah mudah ini untuk mengelola dan menyorot teks hasil OCR Anda agar data dapat diekstraksi dengan akurat.
            </p>

            <ol class="list-decimal list-inside space-y-6 text-gray-800 text-base mb-4">
                <li>
                    <strong class="font-semibold text-md text-gray-900">1. Periksa Hasil OCR</strong>
                    <p class="ml-5 mt-1 text-gray-600">
                        Amati teks yang ditampilkan di panel kiri. Pastikan setiap kata dan kalimat telah terbaca dengan akurat sesuai dokumen asli.
                    </p>
                </li>
                <li>
                    <strong class="font-semibold text-md text-gray-900">2. Koreksi Teks (Jika Diperlukan)</strong>
                    <ul class="list-disc list-inside ml-5 mt-2 space-y-1 text-gray-600">
                        <li>Jika terdapat kesalahan penulisan, angka yang keliru, atau bagian yang tidak terbaca, <strong>klik langsung pada teks tersebut</strong> di area pratinjau OCR.</li>
                        <li><strong>Edit atau hapus</strong> bagian yang tidak sesuai. Sistem akan secara otomatis menyimpan perubahan</strong> teks dan menyesuaikan posisi highlight yang ada.</li>
                    </ul>
                </li>
                <li>
                    <strong class="font-semibold text-md text-gray-900">3. Anotasi (Highlight) Teks Penting</strong>
                    <ul class="list-disc list-inside ml-5 mt-2 space-y-1 text-gray-600">
                        <li>Untuk menandai informasi penting (misalnya, nomor surat, tanggal, atau penanda tangan), <strong>sorot (blok) teks</strong> yang relevan.</li>
                        <li>Sebuah modal kecil akan muncul. <strong>Pilih kategori</strong> anotasi yang paling sesuai dari daftar yang tersedia.</li>
                        <li>Tekan tombol <strong>"Simpan"</strong> untuk menerapkan highlight. Teks yang Anda sorot akan tampil dengan warna yang khas sesuai kategorinya.</li>
                    </ul>
                </li>
            </ol>

            <div class="text-center">
                <button type="button" onclick="closeOcrHelpModal()"
                    class="inline-flex items-center px-6 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-[#6C88A4] hover:bg-[#2C3E50] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6C88A4] transition ease-in-out duration-150">
                    Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showOcrHelpModal() {
        document.getElementById('ocr-help-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Mencegah scrolling body
    }

    function closeOcrHelpModal() {
        document.getElementById('ocr-help-modal').style.display = 'none';
        document.body.style.overflow = ''; // Mengaktifkan kembali scrolling body
    }

    // Menutup modal ketika overlay diklik
    document.addEventListener('click', function(event) {
        const modalOverlay = document.getElementById('ocr-help-modal');
        // Pastikan klik terjadi pada overlay itu sendiri, bukan pada konten modal di dalamnya
        if (modalOverlay && event.target === modalOverlay) {
            closeOcrHelpModal();
        }
    });
</script>