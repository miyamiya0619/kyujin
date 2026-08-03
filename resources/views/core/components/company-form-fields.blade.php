{{--
    企業情報の入力欄。運営者の登録・編集画面と、掲載企業の自社編集画面で共通。

    status は運営者しか変更できないため、ここには含めない(呼び出し側で出す)。
    掲載企業が自分で掲載停止を解除できてしまうため。
--}}
@props(['company', 'prefectures', 'cities'])

<div class="space-y-5">
    <x-form.field name="name" label="企業名" required>
        <x-form.input name="name" :value="$company->name" required />
    </x-form.field>

    <x-form.field name="name_kana" label="企業名(ふりがな)">
        <x-form.input name="name_kana" :value="$company->name_kana" />
    </x-form.field>

    <x-form.field name="tel" label="電話番号">
        <x-form.input name="tel" type="tel" :value="$company->tel" />
    </x-form.field>

    <x-form.field name="website_url" label="ウェブサイト" help="https:// から入力してください。">
        <x-form.input name="website_url" type="url" :value="$company->website_url" />
    </x-form.field>

    <x-form.field name="postal_code" label="郵便番号" help="例) 123-4567">
        <x-form.input name="postal_code" :value="$company->postal_code" class="!w-40" />
    </x-form.field>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-form.field name="prefecture_id" label="都道府県">
            <x-form.select
                name="prefecture_id"
                :value="$company->prefecture_id"
                :options="$prefectures->pluck('name', 'id')"
                data-prefecture-select />
        </x-form.field>

        <x-form.field name="city_id" label="市区町村" help="都道府県を選ぶと候補が表示されます。">
            <x-form.select
                name="city_id"
                :value="$company->city_id"
                :options="$cities->pluck('name', 'id')" />
        </x-form.field>
    </div>

    <x-form.field name="address" label="住所(番地以降)">
        <x-form.input name="address" :value="$company->address" />
    </x-form.field>

    <x-form.field name="description" label="企業紹介" help="求職者に向けた紹介文です。">
        <x-form.textarea name="description" :value="$company->description" :rows="6" />
    </x-form.field>

    <x-prefecture-city-select-script />

    <x-form.field name="logo" label="ロゴ画像" help="JPEG・PNG・WebP、5MB まで。アップロード時に自動で WebP に変換されます。">
        @if ($company->logo_path)
            <img src="{{ \App\Services\ImageUploadService::url($company->logo_path) }}"
                 alt="現在のロゴ" class="mt-2 h-16 rounded border border-[var(--border)] bg-[var(--surface)] p-1">
        @endif
        <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp"
               class="mt-2 block w-full text-sm text-[var(--ink-soft)]">
    </x-form.field>
</div>
