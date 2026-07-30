{{--
    チェックボックスの複数選択群。資格・こだわり条件で使う。
    $options は id => 表示名、$selected は選択済みの id 配列。
--}}
@props(['name', 'options', 'selected' => []])

<div class="flex flex-wrap gap-x-4 gap-y-2">
    @foreach ($options as $id => $label)
        <label class="flex items-center gap-1.5 text-sm">
            <input type="checkbox" name="{{ $name }}[]" value="{{ $id }}"
                   @checked(in_array($id, old($name, $selected)))
                   class="rounded border-gray-300">
            {{ $label }}
        </label>
    @endforeach
</div>
