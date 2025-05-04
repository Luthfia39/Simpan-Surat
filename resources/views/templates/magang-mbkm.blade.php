@php
  $start = Carbon\Carbon::parse($tgl_mulai);
  $end = Carbon\Carbon::parse($tgl_selesai);
  $totalMonth = $start->diffInMonths($end, true);
@endphp

<x-surat>
  <div class="body-header">
    <h1 class="title">SURAT TUGAS</h1>
    <x-number-layout />
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
        Ketua Departemen Teknik Elektro dan Informatika
        <br>
        Sekolah Vokasi UGM
      </x-info-item>
    </x-info-list>

    <p>Dengan ini menugaskan bahwa mahasiswa yang tersebut di bawah ini,</p>

    <x-info-list tab=2 style="margin-left: 24px">
      <x-info-item label="Nama">
        {{ $nama }}
      </x-info-item>
      <x-info-item label="NIM">
        {{ $nim }}
      </x-info-item>
      <x-info-item label="Prodi">
        {{ $prodi }} 
      </x-info-item>
      <x-info-item label="Dosen Pembimbing">
        {{ $dospem }}
      </x-info-item>
    </x-info-list>
    
    <p class="justify">Untuk mengikuti kegiatan Magang MBKM yang diselenggarakan oleh {{ $penyelenggara }} selama {{ floor($totalMonth) }} bulan, dimulai tanggal {{ $start->translatedFormat('j F Y'); }} s.d. {{ $end->translatedFormat('j F Y'); }}. <u>Program ini untuk dapat dilaksanakan sesuai dengan Kurikulum Kampus Merdeka.</u> </p>
    <p class="justify">Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
  </div>
  <x-sign-layout />
</x-surat>