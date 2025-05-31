<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Livewire memiliki fitur unggahan file sementara untuk menangani file
    | sebelum dikirimkan ke server. Kamu bisa mengkonfigurasi penyimpanan
    | sementara di sini.
    |
    */

    'temporary_file_upload' => [
        'enabled' => false,
        // 'disk' => 'local', // Bisa diubah ke 'public' jika ingin file bisa diakses via URL
        // 'rules' => ['file', 'mimes:pdf', 'max:5120'], // Hanya PDF, max 5MB
        // 'directory' => 'livewire-tmp', // Folder penyimpanan sementara di storage/app/livewire-tmp
        // 'preview' => true, // Aktifkan preview file sebelum diunggah
    ],

];
