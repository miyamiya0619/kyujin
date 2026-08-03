@props(['name', 'type' => 'text', 'value' => null])

<input
    id="{{ $name }}"
    name="{{ $name }}"
    type="{{ $type }}"
    value="{{ old($name, $value) }}"
    {{ $attributes->merge(['class' => 'mt-1 w-full rounded border border-[var(--border)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--theme-color,var(--theme-color-fallback))]']) }}>
