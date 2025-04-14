{{--
    type = button, submit, menu, reset
    variant = primary, secondary, danger, warning, success, info, light, dark
    size = sm, md, lg,
    width = full, half
    disabled = true, false
    customColor = random color that you want
    fontWeight = bold, bolder, semibold, medium, light, lighter, normal, italic
    fontSize = 1,2,3,4,5,6
--}}

{{-- @props(['type', 'variant', 'size', 'width', 'disabled', 'customColor', 'fontWeight', 'fontSize']) --}}

@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'width',
    'disabled' => false,
    'customColor',
    'fontWeight' => 'normal',
    'fontSize' => 6,
    'href' => null,
])


@php
    $style = $customColor ? "background-color: {$customColor}; border-color: {$customColor};" : '';
    $btnClass = $customColor ? 'text-white' : "btn-{$variant}";
    $widthClass = match ($width) {
        'full' => 'w-100',
        'half' => 'w-50',
        default => '',
    };
    $commonClasses = "btn {$btnClass} btn-{$size} {$widthClass} shadow-sm fw-{$fontWeight} fs-{$fontSize} px-2 rounded-3";
@endphp


{{-- <button
    type="{{ $type }}"
    class="btn {{ $btnClass }} btn-{{ $size }} {{ $widthClass }} shadow-sm fw-{{$fontWeight}} fs-{{$fontSize}} px-2 rounded-3"
    style="{{ $style }}"
    aria-disabled="{{$disabled}}"
>
    {{ $slot }}
</button> --}}

@if ($href)
    <a href="{{ $href }}" class="{{ $commonClasses }}" style="{{ $style }}" role="button">
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $commonClasses }}" style="{{ $style }}"
        aria-disabled="{{ $disabled }}" {{ $disabled ? 'disabled' : '' }}>
        {{ $slot }}
    </button>
@endif
