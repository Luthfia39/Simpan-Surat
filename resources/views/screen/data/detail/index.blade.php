@extends('layouts.app')

@section('title', 'Detail Surat')

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <h1 class="fs-2 mb-2 fw-semibold">Informasi Lengkap Surat</h1>
            <p class="mb-4 fs-4">Halaman ini menampilkan rincian lengkap dari surat yang dipilih.</p>

            <div class="card">
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Jenis Surat</dt>
                        <dd class="col-sm-9">{{ $surat->type }}</dd>

                        <dt class="col-sm-3">Nomor Surat</dt>
                        <dd class="col-sm-9">{{ $surat->nomor_surat }}</dd>

                        <dt class="col-sm-3">Perihal</dt>
                        <dd class="col-sm-9">{{ $surat->perihal }}</dd>

                        <dt class="col-sm-3">Penerima</dt>
                        <dd class="col-sm-9">{{ $surat->penerima }}</dd>

                        <dt class="col-sm-3">Isi</dt>
                        <dd class="col-sm-9">
                            <pre>{{ $surat->isi_surat }}</pre>
                        </dd>

                        <dt class="col-sm-3">Tanggal</dt>
                        <dd class="col-sm-9">{{ $surat->tanggal }}</dd>
                    </dl>

                    <div class="d-flex gap-2 mt-4">
                        {{-- Tombol Download --}}
                        <x-button class="btn btn-success" tooltip="Download Surat"
                            href="{{ route('download', $surat->id) }}">
                            {{-- <a href="{{ route('surat.download', $surat->id) }}" class="btn btn-success"> --}}
                            <i class="fa fa-download"></i> Download Surat
                        </x-button>

                        {{-- Tombol Delete --}}
                        <form action="{{ route('delete', $surat->id) }}" method="post"
                            id="delete-form-{{ $surat->id }}">
                            @csrf
                            <x-button variant="danger" tooltip="Hapus Surat"
                                onclick="confirmDelete({!! json_encode($surat->id) !!})">
                                <i class="fa-regular fa-trash-can"></i> Hapus Surat
                            </x-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('customJs')
    <script>
        function confirmDelete(id) {
            event.preventDefault();

            Swal.fire({
                title: 'Yakin ingin menghapus surat ini?',
                text: "Data yang sudah dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        @if (session('file_not_found'))
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: '{{ session('file_not_found') }}',
            });
        @endif
    </script>
@endsection
