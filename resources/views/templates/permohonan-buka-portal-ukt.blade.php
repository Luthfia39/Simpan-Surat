@php

  // Pastikan $kelompok selalu array
  $formSchemaItems = is_array($kelompok ?? null) ? $kelompok : []; 

  // Inisialisasi array kosong untuk menyimpan data anggota kelompok yang sudah diflatten
  $anggotaKelompokList = [];

  // Iterasi setiap item di level UUID (level paling atas dari $formSchemaItems)
  foreach ($formSchemaItems as $uuidKey => $itemData) {
      // Pastikan ada kunci 'data_surat' dan 'kelompok' di dalam item ini
      if (isset($itemData['data_surat']['kelompok']) && is_array($itemData['data_surat']['kelompok'])) {
          $anggota = $itemData['data_surat']['kelompok']; // Ambil array 'kelompok'

          // Tambahkan data anggota ini ke daftar final
          $anggotaKelompokList[] = [
              'nama' => $anggota['nama'] ?? '-',
              'nim' => $anggota['nim'] ?? '-',
              'prodi' => $anggota['prodi'] ?? '-',
          ];
      }
  }
@endphp

<x-surat>
    <span class="tgl-right">Yogyakarta, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('j F Y') }}</span>

    <x-info-list style="margin-top: 24px">
      <x-info-item label="Nomor">
        {{ $nomor_surat ?? '1' }}/UN1/SV.2-TEDI/AKM/PJ/{{ date("Y") }}
      </x-info-item>
      <x-info-item label="Hal">
        Permohonan Pembukaan Portal Pembayaran UKT
      </x-info-item>
    </x-info-list>

    <x-info-list style="margin-top: 24px;margin-bottom: 24px">
        <x-info-item label="Kepada">
          {!! nl2br(e($tujuan)) !!}
        </x-info-item>
    </x-info-list>
      
    <div class="body-main" style="margin-left: 70px">
        <p class="justify">Dengan hormat,<br><br>Sehubungan dengan keterlambatan pembayaran UKT semester {{ $semester }} TA. {{ $thn_akademik }} bagi mahasiswa Sarjana Terapan Departemen Teknik Elektro dan Informatika berikut:</p>
        @if (!empty($anggotaKelompokList))
        <table class="table">
            <tr>
            <th>No</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Prodi</th>
            </tr>
            @foreach ($anggotaKelompokList as $anggota) 
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $anggota['nama'] }}</td>
                <td>{{ $anggota['nim'] }}</td>
                <td>{{ $anggota['prodi'] }}</td>
            </tr>
            @endforeach
        </table>
        {{-- @else --}}
        @endif
        <p class="justify" style="margin-top: 15px">Sehubungan dengan hal tersebut, kami mohon izin untuk dapat dibukakan portal pembayaran bagi mahasiswa tersebut.</p>
        <p class="justify" style="margin-top: 15px">Atas perhatian dan kerja samanya kami ucapkan terima kasih.</p>
        </div>
    <x-sign-layout :atasNama=false :tanggal=false />
  </x-surat>
  