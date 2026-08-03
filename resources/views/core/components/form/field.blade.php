{{-- 入力欄の共通ラッパー。ラベル・必須マーク・エラー文言の出し方を揃える。 --}}
@props(['name', 'label', 'required' => false, 'help' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-[var(--ink-soft)]">
        {{ $label }}
        @if ($required)
            <span class="ml-1 rounded bg-[var(--danger-bg)] px-1 text-xs text-[var(--danger)]">必須</span>
        @endif
    </label>

    {{ $slot }}

    @if ($help)
        <p class="mt-1 text-xs text-[var(--muted)]">{{ $help }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
