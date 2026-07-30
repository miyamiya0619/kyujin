{{--
    セレクトボックス。$options は id => 表示名 の連想配列。
    選択肢は必ず selectable() を通したものを渡すこと(CLAUDE.md 3.7)。
--}}
@props(['name', 'options', 'value' => null, 'placeholder' => '選択してください'])

<select
    id="{{ $name }}"
    name="{{ $name }}"
    {{ $attributes->merge(['class' => 'mt-1 w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm focus:border-gray-500 focus:outline-none']) }}>
    <option value="">{{ $placeholder }}</option>
    @foreach ($options as $optionValue => $label)
        <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $label }}</option>
    @endforeach
</select>
