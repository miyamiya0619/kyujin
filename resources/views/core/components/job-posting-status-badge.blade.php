@props(['status'])

@php
    [$label, $bg, $fg] = match ($status) {
        'draft' => ['下書き', 'var(--bg)', 'var(--ink-soft)'],
        'pending' => ['審査待ち', 'var(--warning-bg)', 'var(--warning)'],
        'rejected' => ['差戻し', 'var(--danger-bg)', 'var(--danger)'],
        'published' => ['公開中', 'var(--success-bg)', 'var(--success)'],
        'closed' => ['掲載終了', 'var(--bg)', 'var(--muted)'],
        default => [$status, 'var(--bg)', 'var(--ink-soft)'],
    };
@endphp

<span class="rounded px-2 py-1 text-xs font-medium" style="background-color: {{ $bg }}; color: {{ $fg }}">{{ $label }}</span>
