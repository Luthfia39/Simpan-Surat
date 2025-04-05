@extends('layouts.app')
@section('title')
    List Dokumen
@endsection
@section('styles')
    <link href="DataTables/datatables.min.css" rel="stylesheet">
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
                                <td class="text-truncate text-center text-truncate" style="width: 200px">
                                    {{ $data['isi_surat'] }}</td>
                                <td class="text-center">Penanda tangan</td>
                                <td class="text-center">{{ $data['tanggal'] }}</td>
                                <td class="text-center">
                                    <x-button customColor="#833625" disabled="true">Surat Keluar</x-button>
                                </td>
                                <td class="text-center">
                                    <x-button variant="primary">
                                        <i class="fa-regular fa-eye"></i>
                                    </x-button>
                                    <x-button variant="danger">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </x-button>
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
@endsection
