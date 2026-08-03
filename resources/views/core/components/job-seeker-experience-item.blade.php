{{--
    職務経歴 1 件分の編集フォーム。
    並び替えは「↑」「↓」ボタン方式(ドラッグ&ドロップ用の JS ライブラリを増やさない判断)。
--}}
@props(['experience', 'isFirst' => false, 'isLast' => false])

<div class="rounded border border-[var(--border)] p-4" data-experience-id="{{ $experience->id }}">
    <div class="mb-2 flex justify-end gap-2 text-xs text-[var(--muted)]">
        <button type="button" class="js-move-up disabled:opacity-30" @disabled($isFirst)>↑ 上へ</button>
        <button type="button" class="js-move-down disabled:opacity-30" @disabled($isLast)>↓ 下へ</button>
    </div>
    <form method="POST" action="{{ route('seeker.experiences.update', $experience) }}" class="space-y-3">
        @csrf
        @method('PUT')

        <div class="grid gap-3 sm:grid-cols-2">
            <x-form.field name="organization_name" label="事業所名" required>
                <x-form.input name="organization_name" :value="$experience->organization_name" required />
            </x-form.field>

            <x-form.field name="job_title" label="職種">
                <x-form.input name="job_title" :value="$experience->job_title" />
            </x-form.field>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <x-form.field name="started_on" label="在籍開始年月">
                <x-form.input name="started_on" type="date" :value="$experience->started_on?->toDateString()" />
            </x-form.field>

            <x-form.field name="ended_on" label="在籍終了年月">
                <x-form.input name="ended_on" type="date" :value="$experience->ended_on?->toDateString()"
                              @if ($experience->isCurrent()) disabled @endif />
                <label class="mt-1 flex items-center gap-2 text-xs text-[var(--ink-soft)]">
                    <input type="checkbox" name="is_current" value="1" @checked($experience->isCurrent())
                           onchange="this.closest('form').querySelector('[name=ended_on]').disabled = this.checked"
                           class="rounded border-[var(--border)]">
                    在籍中
                </label>
            </x-form.field>
        </div>

        <x-form.field name="description" label="業務内容">
            <x-form.textarea name="description" :value="$experience->description" :rows="3" />
        </x-form.field>

        <div class="flex items-center justify-between">
            <button type="submit" class="text-sm font-medium hover:underline" style="color: var(--theme-color)">
                この内容で更新する
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('seeker.experiences.destroy', $experience) }}"
          onsubmit="return confirm('この職務経歴を削除しますか?')" class="mt-2">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-xs text-red-600 hover:underline">削除する</button>
    </form>
</div>
