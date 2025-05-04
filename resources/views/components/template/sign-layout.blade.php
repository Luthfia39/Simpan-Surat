@props(['tanggal' => true, 'atasNama' => false])

<div class="ttd">
  @if($tanggal)
    <span>Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('j F Y') }}</span>
  @endif
  @if($atasNama)
    <span>a.n Wakil Dekan Bidang Akademik dan Kemahasiswaan</span>
  @endif
  <span>Ketua Departemen Teknik Elektro dan Informatika</span>
  <span style="text-decoration: underline; margin-top: 60pt">{{ config('constant.kadep.name') }}</span>
  <span>NIKA. {{ config('constant.kadep.nika') }}</span>
</div>