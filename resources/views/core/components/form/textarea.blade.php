@props(['name', 'value' => null, 'rows' => 5])

<textarea
    id="{{ $name }}"
    name="{{ $name }}"
    rows="{{ $rows }}"
    {{ $attributes->merge(['class' => 'mt-1 w-full rounded border border-[var(--border)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--theme-color,var(--theme-color-fallback))]']) }}>{{ old($name, $value) }}</textarea>
