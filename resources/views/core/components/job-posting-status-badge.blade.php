@props(['status'])

@php
    [$label, $classes] = match ($status) {
        'draft' => ['下書き', 'bg-gray-100 text-gray-700'],
        'pending' => ['審査待ち', 'bg-yellow-100 text-yellow-800'],
        'rejected' => ['差戻し', 'bg-red-100 text-red-800'],
        'published' => ['公開中', 'bg-green-100 text-green-800'],
        'closed' => ['掲載終了', 'bg-gray-200 text-gray-700'],
        default => [$status, 'bg-gray-100 text-gray-700'],
    };
@endphp

<span class="rounded px-2 py-1 text-xs font-medium {{ $classes }}">{{ $label }}</span>
