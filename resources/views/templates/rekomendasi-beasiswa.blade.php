<x-surat>
  <div class="body-header">
    <h1 class="title">SURAT REKOMENDASI BEASISWA</h1>
    <x-number-layout />
  </div>
  <div class="body-main">
    <p>Ketua Departemen Teknik Elektro dan Informatika Sekolah Vokasi dengan ini menyetujui saudara:</p>
    <x-info-list style="margin-left: 24px">
      <x-info-item label="Nama">
        {{ $nama }}
      </x-info-item>
      <x-info-item label="NIM">
        {{ $nim }}
      </x-info-item>
      <x-info-item label="Prodi">
        {{-- dibuat panjang  --}}
        {{ Major::getNameByCode($prodi) }} 
      </x-info-item>
      <x-info-item label="IPK">
        {{ $ipk }}
      </x-info-item>
      <x-info-item label="SKS">
        {{ $sks }}
      </x-info-item>
    </x-info-list>
    <p class="justify">Untuk diusulkan sebagai calon penerima Beasiswa {{ $beasiswa }}.</p>
    <p class="justify">Menurut pengamatan kami yang bersangkutan berkelakuan baik dan pantas diberikan beasiswa.</p>
    </div>
  <x-sign-layout />
</x-surat>
