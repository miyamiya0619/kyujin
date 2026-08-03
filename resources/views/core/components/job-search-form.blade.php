{{--
    求人検索フォーム。トップページ(簡易版)と検索結果ページ(詳細版)で共通利用。

    都道府県を変更すると Ajax で市区町村の選択肢を更新する
    (`x-prefecture-city-select-script`)。フォーム自体の送信は
    「この条件で探す」ボタンを押したときだけ行う。
--}}
@props(['prefectures', 'cities', 'facilityTypes' => null, 'jobCategories' => null, 'employmentTypes' => null, 'jobFeatures' => null, 'compact' => false])

<form method="GET" action="{{ route('public.jobs.index') }}" class="card p-4">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="prefecture_id" class="block text-xs text-[var(--muted)]">都道府県</label>
            <select id="prefecture_id" name="prefecture_id" data-prefecture-select
                    class="mt-1 w-full rounded border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm">
                <option value="">指定なし</option>
                @foreach ($prefectures as $prefecture)
                    <option value="{{ $prefecture->id }}" @selected(request()->integer('prefecture_id') === $prefecture->id)>
                        {{ $prefecture->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="city_id" class="block text-xs text-[var(--muted)]">市区町村</label>
            <select id="city_id" name="city_id"
                    class="mt-1 w-full rounded border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm">
                <option value="">指定なし</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}" @selected(request()->integer('city_id') === $city->id)>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <x-prefecture-city-select-script />

        @if ($jobCategories)
            <div>
                <label for="job_category_id" class="block text-xs text-[var(--muted)]">職種</label>
                <select id="job_category_id" name="job_category_id"
                        class="mt-1 w-full rounded border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm">
                    <option value="">指定なし</option>
                    @foreach ($jobCategories as $category)
                        <option value="{{ $category->id }}" @selected(request()->integer('job_category_id') === $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($employmentTypes)
            <div>
                <label for="employment_type_id" class="block text-xs text-[var(--muted)]">雇用形態</label>
                <select id="employment_type_id" name="employment_type_id"
                        class="mt-1 w-full rounded border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm">
                    <option value="">指定なし</option>
                    @foreach ($employmentTypes as $type)
                        <option value="{{ $type->id }}" @selected(request()->integer('employment_type_id') === $type->id)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @unless ($compact)
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @if ($facilityTypes)
                <div>
                    <label for="facility_type_id" class="block text-xs text-[var(--muted)]">施設形態</label>
                    <select id="facility_type_id" name="facility_type_id"
                            class="mt-1 w-full rounded border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm">
                        <option value="">指定なし</option>
                        @foreach ($facilityTypes as $facilityType)
                            <option value="{{ $facilityType->id }}" @selected(request()->integer('facility_type_id') === $facilityType->id)>
                                {{ $facilityType->displayName() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="keyword" class="block text-xs text-[var(--muted)]">キーワード</label>
                <input id="keyword" name="keyword" value="{{ request('keyword') }}"
                       class="mt-1 w-full rounded border border-[var(--border)] px-3 py-2 text-sm">
            </div>

            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="night_shift_only" value="1" @checked(request()->boolean('night_shift_only'))
                           class="rounded border-[var(--border)]">
                    夜勤ありのみ
                </label>
            </div>
        </div>

        @if ($jobFeatures)
            <div class="mt-3">
                <p class="text-xs text-[var(--muted)]">こだわり条件</p>
                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                    @foreach ($jobFeatures as $feature)
                        <label class="flex items-center gap-1.5 text-sm">
                            <input type="checkbox" name="feature_ids[]" value="{{ $feature->id }}"
                                   @checked(in_array($feature->id, (array) request('feature_ids', [])))
                                   class="rounded border-[var(--border)]">
                            {{ $feature->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    @endunless

    <button type="submit" class="btn-primary mt-4">
        この条件で探す
    </button>
</form>
