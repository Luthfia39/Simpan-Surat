@php

  // Pastikan $kelompok selalu array
  $formSchemaItems = is_array($kelompok_arahan ?? null) ? $kelompok_arahan : [];
  $formSchemaItems2 = is_array($kelompok_permohonan ?? null) ? $kelompok_permohonan : [];

  // Inisialisasi array kosong untuk menyimpan data anggota kelompok yang sudah diflatten
  $anggotaKelompokList = [];
  $anggotaKelompokList2 = [];

  // Iterasi setiap item di level UUID (level paling atas dari $formSchemaItems)
  foreach ($formSchemaItems as $uuidKey => $itemData) {
      // Pastikan ada kunci 'data_surat' dan 'kelompok' di dalam item ini
      if (isset($itemData['data_surat']['kelompok_arahan']) && is_array($itemData['data_surat']['kelompok_arahan'])) {
          $anggota = $itemData['data_surat']['kelompok_arahan']; // Ambil array 'kelompok'

          // Tambahkan data anggota ini ke daftar final
          $anggotaKelompokList[] = [
              'mata_kuliah' => $anggota['mata_kuliah'] ?? '-',
              'kode_kelas' => $anggota['kode_kelas'] ?? '-',
              'prodi' => $anggota['prodi'] ?? '-',
              'jumlah_mahasiswa' => $anggota['jumlah_mahasiswa'] ?? '-',
              'hari' => $anggota['hari'] ?? '-',
              'sesi' => $anggota['sesi'] ?? '-',
              'pukul' => $anggota['pukul'] ?? '-',
          ];
      }
  }

  foreach ($formSchemaItems2 as $uuidKey => $itemData) {
      // Pastikan ada kunci 'data_surat' dan 'kelompok' di dalam item ini
      if (isset($itemData['data_surat']['kelompok_permohonan']) && is_array($itemData['data_surat']['kelompok_permohonan'])) {
          $anggota = $itemData['data_surat']['kelompok_permohonan']; // Ambil array 'kelompok'

          // Tambahkan data anggota ini ke daftar final
          $anggotaKelompokList2[] = [
              'mata_kuliah' => $anggota['mata_kuliah'] ?? '-',
              'kode_kelas' => $anggota['kode_kelas'] ?? '-',
              'prodi' => $anggota['prodi'] ?? '-',
              'hari' => $anggota['hari'] ?? '-',
              'sesi' => $anggota['sesi'] ?? '-',
              'pukul' => $anggota['pukul'] ?? '-',
              'dosen' => $anggota['dosen'] ?? '-',
          ];
      }
  }
@endphp

<x-surat>
  <span class="tgl-right">Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('j F Y') }}</span>
  <div class="body-main">
    <x-info-list style="margin-top: 24px">
      <x-info-item label="Nomor">
        {{ $nomor_surat ?? '1' }}/UN1/SV2-TEDI/AKM/PJ/{{ date("Y") }}
      </x-info-item>
      <x-info-item label="Hal">
        Permohonan Dosen Pengampu
      </x-info-item>
    </x-info-list>

    <x-info-list style="margin-top: 24px;margin-bottom: 24px">
      <x-info-item label="Kepada">
        {!! nl2br(e($tujuan)) !!}
      </x-info-item>
    </x-info-list>

    <p class="justify">Sehubungan dengan akan dilaksanakannya kegiatan perkuliahan Semester {{ $semester }} TA. {{ $thn_akademik }} di Departemen Teknik Elektro dan Informatika Sekolah Vokasi Universitas Gadjah Mada, kami mohon bantuan menugaskan dosen dari {{ $departemen }} untuk mengampu mata kuliah berikut.</p>

    @if (!empty($anggotaKelompokList))
    <p>Dosen pengampu mata kuliah sesuai arahan {{ $departemen }}:</p>
    
    <table class="table">
      <tr>
        <th>Mata Kuliah</th>
        <th>Kode Kelas</th>
        <th>Prodi</th>
        <th>Jumlah Mahasiswa</th>
        <th>Jadwal</th>
        <th>Sesi</th>
        <th>Pukul</th>
      </tr>
      @foreach ($anggotaKelompokList as $anggota) 
        <tr>
            <td>{{ $anggota['mata_kuliah'] }}</td>
            <td>{{ $anggota['kode_kelas'] }}</td>
            <td>{{ $anggota['prodi'] }}</td>
            <td>{{ $anggota['jumlah_mahasiswa'] }}</td>
            <td>{{ $anggota['hari'] }}</td>
            <td>{{ $anggota['sesi'] }}</td>
            <td>{{ $anggota['pukul'] }}</td>
        </tr>
      @endforeach
    </table>
    {{-- @else --}}
    @endif

    @if (!empty($anggotaKelompokList2))
    <p>Dosen pengampu mata kuliah permohonan dari Departemen Teknik Elektro dan Informatika:</p>

    <table class="table">
        <tr>
          <th>Mata Kuliah</th>
          <th>Kode Kelas</th>
          <th>Prodi</th>
          <th>Jadwal</th>
          <th>Sesi</th>
          <th>Pukul</th>
          <th>Dosen Pengampu</th>
        </tr>
        @foreach ($anggotaKelompokList2 as $anggota) 
          <tr>
            <td>{{ $anggota['mata_kuliah'] }}</td>
            <td>{{ $anggota['kode_kelas'] }}</td>
            <td>{{ $anggota['prodi'] }}</td>
            <td>{{ $anggota['hari'] }}</td>
            <td>{{ $anggota['sesi'] }}</td>
            <td>{{ $anggota['pukul'] }}</td>
            <td>{{ $anggota['dosen'] }}</td>
          </tr>
        @endforeach
      </table>
      {{-- @else --}}
      @endif

  <p>Demikian permohonan ini kami sampaikan, atas perhatian dan kerja samanya kami ucapkan terima kasih.</p>
  </div>

  <x-sign-layout :atasNama=false :tanggal=false />

</x-surat>