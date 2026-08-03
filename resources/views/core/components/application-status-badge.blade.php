@props(['status'])

@php
    [$label, $bg, $fg] = match ($status) {
        'new' => ['新規応募', 'var(--warning-bg)', 'var(--warning)'],
        'document_screening' => ['書類選考中', 'var(--info-bg)', 'var(--info)'],
        'interview_arranging' => ['面接調整中', 'var(--info-bg)', 'var(--info)'],
        'interviewed' => ['面接済', 'var(--info-bg)', 'var(--info)'],
        'offer' => ['内定', 'var(--success-bg)', 'var(--success)'],
        'hired' => ['入社', 'var(--brand-tint)', 'var(--theme-color, var(--theme-color-fallback))'],
        'rejected' => ['不採用', 'var(--bg)', 'var(--muted)'],
        'declined' => ['辞退', 'var(--bg)', 'var(--muted)'],
        default => [$status, 'var(--bg)', 'var(--ink-soft)'],
    };
@endphp

<span class="rounded px-2 py-1 text-xs font-medium" style="background-color: {{ $bg }}; color: {{ $fg }}">{{ $label }}</span>
