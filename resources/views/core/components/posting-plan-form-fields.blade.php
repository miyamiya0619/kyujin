{{-- 掲載プランの入力欄。作成・編集で共通(SPEC.md 6.1)。 --}}
@props(['postingPlan'])

<div class="space-y-5">
    <x-form.field name="name" label="プラン名" required help="例) 無料枠 / スタンダード / プレミアム">
        <x-form.input name="name" :value="$postingPlan->name" required />
    </x-form.field>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-form.field name="max_job_postings" label="同時掲載可能件数" help="空欄で無制限">
            <x-form.input name="max_job_postings" type="number" min="1" :value="$postingPlan->max_job_postings" />
        </x-form.field>

        <x-form.field name="posting_duration_days" label="掲載期間(日数)" help="空欄で無期限">
            <x-form.input name="posting_duration_days" type="number" min="1" :value="$postingPlan->posting_duration_days" />
        </x-form.field>

        <x-form.field name="max_workplaces" label="事業所登録数の上限" help="空欄で無制限">
            <x-form.input name="max_workplaces" type="number" min="1" :value="$postingPlan->max_workplaces" />
        </x-form.field>
    </div>

    <x-form.field name="monthly_price" label="月額表示価格(円)" help="表示のみ。課金・請求はこのシステムでは行いません。">
        <x-form.input name="monthly_price" type="number" min="0" :value="$postingPlan->monthly_price" class="!w-40" />
    </x-form.field>

    <x-form.field name="is_featured" label="上位表示">
        <label class="mt-1 flex items-center gap-2 text-sm">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1"
                   @checked(old('is_featured', $postingPlan->is_featured))
                   class="rounded border-gray-300">
            このプランの求人を検索結果で優先表示する
        </label>
    </x-form.field>

    <x-form.field name="is_enabled" label="有効">
        <label class="mt-1 flex items-center gap-2 text-sm">
            <input type="hidden" name="is_enabled" value="0">
            <input type="checkbox" name="is_enabled" value="1"
                   @checked(old('is_enabled', $postingPlan->is_enabled ?? true))
                   class="rounded border-gray-300">
            有効(無効にすると新規割当の選択肢に出なくなります)
        </label>
    </x-form.field>
</div>
