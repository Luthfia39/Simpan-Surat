
<x-surat>
  <div class="body-header">
    <h1 class="title">SURAT KETERANGAN ALUMNI</h1>
    <x-number-layout />
  </div>
  <div class="body-main">
    <p>Yang bertanda tangan dibawah ini:</p>
    <x-info-list tab=2 style="margin-left: 24px">
      <x-info-item label="Nama">
        {{ config('constant.kadep.name') }}
      </x-info-item>
      <x-info-item label="Jabatan">
        Ketua Departemen Teknik Elektro dan Informatika
        <br>
        Sekolah Vokasi UGM
      </x-info-item>
    </x-info-list>

    <p>Menerangkan bahwa:</p>

    <x-info-list tab=2 style="margin-left: 24px">
      <x-info-item label="Nama">
        {{ $nama }}
      </x-info-item>
      <x-info-item label="NIM">
        {{ $nim }}
      </x-info-item>
      <x-info-item label="Program Studi">
        {{ $prodi }} <br> Sekolah Vokasi Universitas Gadjah Mada
      </x-info-item>
    </x-info-list>
    
    <p class="justify">Adalah benar-benar alumni Sekolah Vokasi Universitas Gadjah Mada tahun {{ $thn_lulus }} dari Program Studi {{ $prodi }}</p>
    <p class="justify">Demikian surat keterangan ini dibuat {{ $keterangan }}.</p>
  </div>
  <x-sign-layout :atasNama=true />
</x-surat>