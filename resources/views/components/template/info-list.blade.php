@props(['tab' => 1, 'style' => ""])

<table class="info-list t-{{ $tab }}" style="{{ $style }}">
  <style>.t-{{ $tab }} .label{width:{{ $tab * 64 }}px}</style>
  {{ $slot }}
</table>