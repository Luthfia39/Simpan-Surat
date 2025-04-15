@extends('layouts.app')
@section('title')
    List Dokumen
@endsection
@section('styles')
    <link href="DataTables/datatables.min.css" rel="stylesheet">
    <style>
        div#tableheader {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        select.dt-input,
        input.dt-input {
            border-radius: 0.5rem;
            padding: 0.1rem;
        }

        input.dt-input {
            flex: 1;
            padding: 0.2rem;
            border: 1px solid #ccc;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        input.dt-input:focus {
            border-color: var(--bs-body-color);
        }

        .dt-search {
            display: flex;
            flex-direction: row;
            gap: 0.3rem;
            align-items: center;
        }

        thead {
            background-color: var(--bs-border-color);
        }

        .dt-paging>nav {
            display: flex;
            gap: 0.5rem;
            margin: 1rem 1rem
        }

        .dt-paging-button {
            padding: 0.4rem 0.8rem;
            border: 1px solid var(--bs-link-color);
            background-color: white;
            color: #333;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.2s, color 0.2s;
        }

        .dt-paging-button:hover {
            background-color: var(--bs-link-hover-color);
            color: white;
            border-color: var(--bs-link-hover-color);
        }

        .dt-paging-button.disabled {
            opacity: 0.5;
            pointer-events: none;
            cursor: default;
        }

        .isi-truncated {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            min-height: 5em;
            line-height: 1.5em;
            word-break: break-word;
        }
    </style>
@endsection
@section('content')
    <main class="content">
        <div class="container-fluid p-0">

            <h1 class="h3 mb-3">Daftar Surat</h1>

            <div class="table-responsive">
                <table class="table " id="tableBase">
                    <thead style="background-color: #e6e6e6">
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Perihal</th>
                            <th class="text-center">Nomor surat</th>
                            <th class="text-center">Penerima surat</th>
                            <th class="text-center">Isi</th>
                            <th class="text-center">Penanda tangan</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Jenis surat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($surats as $data)
                            <tr style="color: #969696">
                                <td class="text-center">No</td>
                                <td class="text-center">Jenis/perihal</td>
                                <td class="text-center">{{ $data['nomor_surat'] }}</td>
                                <td class="text-center">{{ $data['penerima'] }}</td>
                                <td class="text-center isi-truncated" style="width: 200px">
                                    {{ $data['isi_surat'] }}</td>
                                <td class="text-center">Penanda tangan</td>
                                <td class="text-center">{{ $data['tanggal'] }}</td>
                                <td class="text-center">
                                    @if ($data['type'] === 'Surat Tugas')
                                        <span class="badge bg-success">Surat Tugas</span>
                                    @elseif ($data['jenis'] === 'Surat Keterangan')
                                        <span class="badge bg-info">Surat Keterangan</span>
                                    @elseif ($data['jenis'] === 'Surat Permohonan')
                                        <span class="badge bg-warning">Surat Permohonan</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $data['type'] }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2">
                                        <x-button variant="primary" href="{{ route('detail', $data['id']) }}"
                                            tooltip="Detail Surat">
                                            <i class="fa-regular fa-eye"></i>
                                        </x-button>
                                        <form action="{{ route('delete', $data['id']) }}" method="post"
                                            id="delete-form-{{ $data['id'] }}">
                                            @csrf
                                            <x-button variant="danger" tooltip="Hapus Surat"
                                                onclick="confirmDelete({!! json_encode($data['id']) !!})">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </x-button>
                                        </form>
                                    </div>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </main>
@endsection

@section('customJs')
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/tables.js') }}"></script>
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
    </script>
@endsection
