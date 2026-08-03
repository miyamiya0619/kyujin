{{--
    セレクトボックス。$options は id => 表示名 の連想配列。
    選択肢は必ず selectable() を通したものを渡すこと(CLAUDE.md 3.7)。
--}}
@props(['name', 'options', 'value' => null, 'placeholder' => '選択してください'])

<select
    id="{{ $name }}"
    name="{{ $name }}"
    {{ $attributes->merge(['class' => 'mt-1 w-full rounded border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--theme-color,var(--theme-color-fallback))]']) }}>
    <option value="">{{ $placeholder }}</option>
    @foreach ($options as $optionValue => $label)
        <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $label }}</option>
    @endforeach
</select>
