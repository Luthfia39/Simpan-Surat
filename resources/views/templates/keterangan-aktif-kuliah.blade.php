<x-surat>
  <div class="body-header">
    <h1 class="title">SURAT KETERANGAN AKTIF KULIAH</h1>
    <x-number-layout />
  </div>
  <div class="body-main">
    <p>Yang bertanda tangan dibawah ini:</p>
    <x-info-list tab=2 style="margin-left: 24px">
      <x-info-item label="Nama">
        {{config('constant.kadep.name')}}
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
        {{$nama}}
      </x-info-item>
      <x-info-item label="NIM">
        {{$nim}}
      </x-info-item>
      <x-info-item label="Program Studi">
        {{$prodi}}
      </x-info-item>
    </x-info-list>

    <p>Anak dari:</p>
    <x-info-list tab=2 style="margin-left: 24px">
      <x-info-item label="Nama">
        {{$nama_ortu}}
      </x-info-item>
      <x-info-item label="Pekerjaan">
        {{$pekerjaan}}
      </x-info-item>
      <x-info-item label="NIP">
        {{$nip}}
      </x-info-item>
      <x-info-item label="Pangkat/Gol">
        {{$pangkat}}
      </x-info-item>
      <x-info-item label="Instansi">
        {{$instansi}}
      </x-info-item>
    </x-info-list>

    <p class="justify">Adalah benar-benar mahasiswa Sekolah Vokasi Universitas Gadjah Mada yang terdaftar aktif kuliah pada Semester {{ $semester }} Tahun Akademik {{$thn_akademik}}.</p>
    <p class="justify">Adapun surat keterangan ini kami buat sebagai persyaratan {{$keterangan}}.</p>
    <p class="justify">Demikian untuk dapat dipergunakan sebagaimana mestinya.</p>
  </div>
  <x-sign-layout :atasNama=true />
</x-surat>