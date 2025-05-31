@props(['label' => 'Label'])

<tr>
  <td class="label">{{ $label }}</td>
  <td style="width: 12px">:</td>
  <td>{{ $slot }}</td>
</tr>