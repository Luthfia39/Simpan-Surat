@php
  $start = Carbon\Carbon::parse($tgl_mulai);
  $end = Carbon\Carbon::parse($tgl_selesai);

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
              // Tambahkan field lain jika ada (misal: 'prodi_anggota', 'jurusan_anggota')
          ];
      }
  }
@endphp

<x-surat>
  <span class="tgl-right">Yogyakarta, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('j F Y') }}</span>
  <div class="body-main">
    <x-info-list style="margin-top: 24px">
      <x-info-item label="Nomor">
        {{ $nomor_surat ?? '1' }}/UN1/SV.2-TEDI/AKM/PJ/{{ date("Y") }}
      </x-info-item>
      <x-info-item label="Lampiran">
        -
      </x-info-item>
      <x-info-item label="Hal">
        Permohonan Praktik Industri
      </x-info-item>
    </x-info-list>

    <x-info-list style="margin-top: 24px;margin-bottom: 24px">
      <x-info-item label="Kepada">
        {!! nl2br(e($tujuan)) !!}
      </x-info-item>
    </x-info-list>

    <p class="justify">Dengan hormat,<br>Kami mohon izin mengajukan permohonan Kerja Praktik di {{ $tempat }} bagi mahasiswa berikut :</p>

    @if (!empty($anggotaKelompokList) && count($anggotaKelompokList) > 1)
    <table class="table" style="margin-left: 24px;">
      <tr>
        <th style="width: 16px">No</th>
        <th>Nama</th>
        <th>NIM</th>
        <th>Prodi</th>
      </tr>
      @foreach ($anggotaKelompokList as $anggota) 
        <tr>
          <td style="text-align: center">{{ $loop->iteration . '.' }}</td>
          <td>{{ $anggota['nama'] ?? '-' }}</td>
          <td>{{ $anggota['nim'] ?? '-' }}</td>
          <td>{{ $prodi ?? '-' }}</td>
        </tr>
      @endforeach
    </table>
    @else
    <x-info-list tab=2 style="margin-left: 24px">
      <x-info-item label="Nama">
        {{ $anggota['nama'] }}
      </x-info-item>
      <x-info-item label="NIM">
        {{ $anggota['nim'] }}
      </x-info-item>
      <x-info-item label="Prodi">
        {{ $prodi }}
      </x-info-item>
    </x-info-list>
    @endif

  <p class="justify">Adapun periode Praktik Industri yang kami usulkan yaitu mulai tanggal {{ $start->locale('id')->translatedFormat('j F Y') }} s.d. {{ $end->locale('id')->translatedFormat('j F Y') }}. Apabila berkenan untuk konfirmasi lebih lanjut dapat menghubungi Sekretariat Departemen Teknik Elektro dan Informatika SV-UGM dengan nomor telepon 0274 561111 dan email tedi.sv@ugm.ac.id.</p>
  <p>Demikian permohonan ini, atas perhatian dan kerja samanya kami ucapkan terima kasih.</p>
  </div>

  <x-sign-layout :atasNama=true :tanggal=false />

  <div style="clear: both; padding-top: 20px;"><u>TEBUSAN :</u> <br>
    <ol>
      <li>Ketua Prodi {{ $prodi ?? '-' }}</li>
      <li>Mahasiswa yang bersangkutan</li>
    </ol>
  </div>
</x-surat>