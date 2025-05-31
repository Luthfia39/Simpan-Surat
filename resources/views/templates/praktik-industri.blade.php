@php
  $start = Carbon\Carbon::parse($tgl_mulai);
  $end = Carbon\Carbon::parse($tgl_selesai);
  $totalMonth = $start->diffInMonths($end, true);
@endphp

<x-surat>
  <div class="body-header">
    <h1 class="title">SURAT TUGAS</h1>
    <span class="nomor-surat">NO. 1111/UN1/SV.2-TEDI/PREVIEW/2024</span>
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

    @if (isset($kelompok[1]))
    <table class="table">
      <tr>
        <th style="width: 16px">No</th>
        <th>Nama</th>
        <th>NIM</th>
        <th>Prodi</th>
      </tr>
      @foreach (array_values($kelompok) as $anggota)
        <tr>
          <td style="text-align: center">{{ $loop->iteration . '.' }}</td>
          <td>{{ $anggota['nama'] }}</td>
          <td>{{ $anggota['nim'] }}</td>
          <td>{{ $prodi }}</td>
        </tr>
      @endforeach
    </table>
    @else
    <x-info-list tab=2 style="margin-left: 24px">
      <x-info-item label="Nama">
        {{ $kelompok[0]['nama'] }}
      </x-info-item>
      <x-info-item label="NIM">
        {{ $kelompok[0]['nim'] }}
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