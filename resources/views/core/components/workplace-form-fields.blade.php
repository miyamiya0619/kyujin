{{--
    事業所情報の入力欄。運営者の代行編集と、掲載企業の自社編集で共通。
--}}
@props(['workplace', 'facilityTypes', 'prefectures', 'cities'])

<div class="space-y-5">
    <x-form.field name="name" label="事業所名" required>
        <x-form.input name="name" :value="$workplace->name" required />
    </x-form.field>

    <x-form.field name="facility_type_id" label="施設形態">
        <x-form.select
            name="facility_type_id"
            :value="$workplace->facility_type_id"
            :options="$facilityTypes->pluck('name', 'id')" />
    </x-form.field>

    <x-form.field name="postal_code" label="郵便番号" help="例) 123-4567">
        <x-form.input name="postal_code" :value="$workplace->postal_code" class="!w-40" />
    </x-form.field>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-form.field name="prefecture_id" label="都道府県">
            <x-form.select
                name="prefecture_id"
                :value="$workplace->prefecture_id"
                :options="$prefectures->pluck('name', 'id')"
                data-prefecture-select />
        </x-form.field>

        <x-form.field name="city_id" label="市区町村" help="都道府県を選ぶと候補が表示されます。">
            <x-form.select
                name="city_id"
                :value="$workplace->city_id"
                :options="$cities->pluck('name', 'id')" />
        </x-form.field>
    </div>

    <x-prefecture-city-select-script />

    <x-form.field name="address" label="住所(番地以降)">
        <x-form.input name="address" :value="$workplace->address" />
    </x-form.field>

    <x-form.field name="access" label="アクセス" help="例) ○○駅から徒歩5分">
        <x-form.input name="access" :value="$workplace->access" />
    </x-form.field>

    <x-form.field name="description" label="施設紹介">
        <x-form.textarea name="description" :value="$workplace->description" :rows="6" />
    </x-form.field>

    <x-form.field name="photo" label="施設写真" help="JPEG・PNG・WebP、5MB まで。">
        @if ($workplace->photo_path)
            <img src="{{ $workplace->photoUrl() }}" alt="現在の施設写真"
                 class="mt-2 h-32 rounded border border-gray-200 object-cover">
        @endif
        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
               class="mt-2 block w-full text-sm text-gray-700">
    </x-form.field>
</div>
