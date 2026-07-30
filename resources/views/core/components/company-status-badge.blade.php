@props(['status'])

@php
    [$label, $classes] = match ($status) {
        'active' => ['掲載中', 'bg-green-100 text-green-800'],
        'suspended' => ['停止中', 'bg-yellow-100 text-yellow-800'],
        'archived' => ['契約終了', 'bg-gray-200 text-gray-700'],
        default => [$status, 'bg-gray-100 text-gray-700'],
    };
@endphp

<span class="rounded px-2 py-1 text-xs font-medium {{ $classes }}">{{ $label }}</span>
