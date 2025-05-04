@php
  $start = Carbon\Carbon::parse($tgl_mulai);
  $end = Carbon\Carbon::parse($tgl_selesai);
@endphp

<x-surat>
  <span class="tgl-right">Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('j F Y') }}</span>
  <div class="body-main">
    <x-info-list style="margin-top: 24px">
      <x-info-item label="Nomor">
        _____/UN1/SV2-TEDI/AKM/PJ/{{ date("Y") }}
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

  <p class="justify">Adapun periode Praktik Industri yang kami usulkan yaitu mulai tanggal {{ $start->translatedFormat('j F Y') }} s.d. {{ $end->translatedFormat('j F Y') }}. Apabila berkenan untuk konfirmasi lebih lanjut dapat menghubungi Sekretariat Departemen Teknik Elektro dan Informatika SV-UGM dengan nomor telepon 0274 561111 dan email tedi.sv@ugm.ac.id.</p>
  <p>Demikian permohonan ini, atas perhatian dan kerja samanya kami ucapkan terima kasih.</p>
  </div>

  <x-ttd :atasNama=true :tanggal=false />

  <div style="clear: both; padding-top: 20px;"><u>TEBUSAN :</u> <br>
    <ol>
      <li>Ketua Prodi {{ $prodi }}</li>
      <li>Mahasiswa yang bersangkutan</li>
    </ol>
  </div>
</x-surat>