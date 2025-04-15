@extends('layouts.app')
@section('title')
    Unggah Dokumen
@endsection
@section('content')
    <main class="content">
        <div class="container-fluid p-0">

            <h1 class="fs-2 mb-2 fw-semibold">Tambah Surat Resmi ke Sistem</h1>
            <p class="mb-4 fs-4">Gunakan formulir ini untuk mendata surat masuk maupun keluar, agar dapat terarsipkan dengan
                rapi dan mudah ditelusuri.</p>

            <livewire:form-upload />

        </div>
    </main>
@endsection

@section('customJs')
    <script>
        document.addEventListener("livewire:load", function() {
            window.Livewire = Livewire;
            console.log('Livewire loaded');
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // preview nama file
            Livewire.hook('message.received', (message, component) => {
                const preview = document.getElementById('preview');
                if (preview && component.serverMemo.data.fileName) {
                    preview.innerHTML = `
                <span class="badge bg-success p-2">
                    📄 ${component.serverMemo.data.fileName}
                </span>`;
                }
            });

            // notifikasi
            Livewire.on('showSweetAlert', data => {
                if (!data || !Array.isArray(data) || data.length === 0) {
                    console.error("Data tidak valid:", data);
                    return;
                }

                const alertData = data[0];

                if (alertData.type === "success_ocr") {
                    Swal.fire({
                        icon: 'success',
                        title: alertData.title || "Berhasil!",
                        text: alertData.text || "",
                        showDenyButton: false,
                        showCancelButton: true,
                        confirmButtonText: "Simpan",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log("simpan")
                            Livewire.dispatch('saveData');
                        }
                    });
                } else if (alertData.type === "error") {
                    Swal.fire({
                        icon: 'error',
                        title: alertData.message || "Terjadi Kesalahan!",
                    });
                }
            });

            Livewire.on('showResultAlert', data => {
                console.log(data);
                if (!data || !Array.isArray(data) || data.length === 0) {
                    console.error("Data tidak valid:", data);
                    return;
                }

                const alertData = data[0];
                Swal.fire({
                    icon: alertData.type,
                    title: alertData.title,
                    text: alertData.text
                });
            });

        });
    </script>
@endsection
