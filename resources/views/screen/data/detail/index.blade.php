@extends('layouts.app')

@section('title', 'Detail Surat')

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <h1 class="h3 mb-3">Detail Surat</h1>

            <div class="card">
                <div class="card-body">
                    <dl class="row">
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

                        <dt class="col-sm-3">Jenis Surat</dt>
                        <dd class="col-sm-9">{{ $surat->type }}</dd>
                    </dl>

                    <div class="d-flex gap-2 mt-4">
                        {{-- Tombol Download --}}
                        <x-button class="btn btn-success" tooltip="Download Surat">
                            {{-- <a href="{{ route('surat.download', $surat->id) }}" class="btn btn-success"> --}}
                            <i class="fa fa-download"></i> Download Surat
                        </x-button>

                        {{-- Tombol Delete --}}
                        <form action="" method="POST" onsubmit="return confirm('Yakin ingin menghapus surat ini?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" tooltip="Hapus Surat">
                                <i class="fa fa-trash"></i> Hapus Surat
                            </x-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
