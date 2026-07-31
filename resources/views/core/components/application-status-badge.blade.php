@props(['status'])

@php
    [$label, $classes] = match ($status) {
        'new' => ['新規応募', 'bg-red-100 text-red-800'],
        'document_screening' => ['書類選考中', 'bg-yellow-100 text-yellow-800'],
        'interview_arranging' => ['面接調整中', 'bg-yellow-100 text-yellow-800'],
        'interviewed' => ['面接済', 'bg-blue-100 text-blue-800'],
        'offer' => ['内定', 'bg-green-100 text-green-800'],
        'hired' => ['入社', 'bg-green-200 text-green-900'],
        'rejected' => ['不採用', 'bg-gray-200 text-gray-700'],
        'declined' => ['辞退', 'bg-gray-200 text-gray-700'],
        default => [$status, 'bg-gray-100 text-gray-700'],
    };
@endphp

<span class="rounded px-2 py-1 text-xs font-medium {{ $classes }}">{{ $label }}</span>
