@php
  $start = Carbon\Carbon::parse($tgl_mulai);
  $end = Carbon\Carbon::parse($tgl_selesai);
  $totalMonth = $start->diffInMonths($end, true);

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
  <div class="body-header">
    <h1 class="title">SURAT TUGAS</h1>
    <span class="nomor-surat">NO. {{ $nomor_surat ?? '1' }}/UN1/SV.2-TEDI/AKM/PJ/{{ date("Y") }}</span>
  </div>
  <div class="body-main">
    <p>Yang bertanda tangan dibawah ini:</p>
    <x-info-list>
      <x-info-item label="Nama">
        {{ config('constant.kadep.name') }}
      </x-info-item>
      <x-info-item label="NIKA">
        {{ config('constant.kadep.nika') }}
      </x-info-item>
      <x-info-item label="Jabatan">
        Ketua Departemen Teknik Elektro dan Informatika, Sekolah Vokasi UGM
      </x-info-item>
    </x-info-list>
    <p>Dengan ini menugaskan bahwa mahasiswa yang tersebut di bawah ini,</p>

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

    <p class="justify">Untuk melakukan kegiatan Praktik Industri di {{ $perusahaan }} selama {{ floor($totalMonth) }} bulan dari tanggal {{ $start->translatedFormat('j F Y') }} s.d. {{ $end->translatedFormat('j F Y') }}, dengan dosen pembimbing : {{ $dospem }}</p>
    <p class="justify">Demikian surat tugas ini dibuat, untuk dapat dipergunakan sebagaimana mestinya.</p>
  </div>
  <x-sign-layout />
</x-surat>