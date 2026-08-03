@props(['status'])

@php
    [$label, $bg, $fg] = match ($status) {
        'active' => ['掲載中', 'var(--success-bg)', 'var(--success)'],
        'suspended' => ['停止中', 'var(--warning-bg)', 'var(--warning)'],
        'archived' => ['契約終了', 'var(--bg)', 'var(--muted)'],
        default => [$status, 'var(--bg)', 'var(--ink-soft)'],
    };
@endphp

<span class="rounded px-2 py-1 text-xs font-medium" style="background-color: {{ $bg }}; color: {{ $fg }}">{{ $label }}</span>
