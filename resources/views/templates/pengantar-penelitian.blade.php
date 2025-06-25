@php
  $start = Carbon\Carbon::parse($tgl_mulai);
@endphp

<x-surat>
  <span class="tgl-right">Yogyakarta, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('j F Y') }}</span>
  <div class="body-main">
    <x-info-list style="margin-top: 24px">
      <x-info-item label="Nomor">
        {{ $nomor_surat ?? '1' }}/UN1/SV2-TEDI/AKM/PJ/{{ date("Y") }}
      </x-info-item>
      <x-info-item label="Lampiran">
        -
      </x-info-item>
      <x-info-item label="Hal">
        Permohonan Penelitian Proyek Akhir
      </x-info-item>
    </x-info-list>

    <x-info-list style="margin-top: 24px;margin-bottom: 24px">
      <x-info-item label="Kepada">
        {!! nl2br(e($tujuan)) !!}
      </x-info-item>
    </x-info-list>

    <p class="justify">Dengan hormat,<br>Dengan ini kami mengajukan Penelitian Proyek Akhir di {{ $tempat }} bagi mahasiswa berikut :</p>

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
      <x-info-item label="Departemen">
        Teknik Elektro dan Informatika, Sekolah Vokasi UGM
      </x-info-item>
    </x-info-list>

  <p class="justify">Untuk dapat melaksanakan Penelitian Proyek Akhir mulai bulan {{ $start->locale('id')->translatedFormat('F Y') }}. Topik "{{ $topik }}". Apabila berkenan untuk konfirmasi lebih lanjut dapat menghubungi Sekretariat Departemen Teknik Elektro dan Informatika SV-UGM dengan nomor telpon 0274 561111 dan email tedi.sv@ugm.ac.id.</p>
  <p>Demikian permohonan ini, atas perhatian dan kerja samanya kami ucapkan terima kasih.</p>
  </div>

  <x-sign-layout :atasNama=true :tanggal=false />
</x-surat>