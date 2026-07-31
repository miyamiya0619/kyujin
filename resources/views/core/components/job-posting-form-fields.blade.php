{{--
    求人情報の入力欄。運営者の代行入稿と、掲載企業のセルフ入稿で共通。

    フォーム項目は固定。顧客が項目を追加できる仕組みは作らない(CLAUDE.md 3.3)。
--}}
@props([
    'jobPosting', 'workplaces', 'jobCategories', 'employmentTypes',
    'qualifications', 'jobFeatures', 'selectedQualificationIds', 'selectedFeatureIds',
])

<div class="space-y-5">
    <x-form.field name="workplace_id" label="事業所" required
                  help="住所・アクセス・施設形態は事業所の登録内容がそのまま使われます。">
        <x-form.select
            name="workplace_id"
            :value="$jobPosting->workplace_id"
            :options="$workplaces->pluck('name', 'id')"
            required />

        @if ($workplaces->isEmpty())
            <p class="mt-1 text-xs text-red-600">
                事業所が登録されていません。先に事業所を登録してください。
            </p>
        @endif
    </x-form.field>

    <x-form.field name="title" label="求人タイトル" required help="例) 【日勤のみ】特養の介護職員 未経験歓迎">
        <x-form.input name="title" :value="$jobPosting->title" required />
    </x-form.field>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-form.field name="job_category_id" label="職種">
            <x-form.select
                name="job_category_id"
                :value="$jobPosting->job_category_id"
                :options="$jobCategories->pluck('name', 'id')" />
        </x-form.field>

        <x-form.field name="employment_type_id" label="雇用形態">
            <x-form.select
                name="employment_type_id"
                :value="$jobPosting->employment_type_id"
                :options="$employmentTypes->pluck('name', 'id')" />
        </x-form.field>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-form.field name="salary_type" label="給与形態">
            <x-form.select
                name="salary_type"
                :value="$jobPosting->salary_type"
                :options="['monthly' => '月給', 'hourly' => '時給', 'daily' => '日給', 'annual' => '年収']" />
        </x-form.field>

        <x-form.field name="salary_min" label="給与(下限・円)">
            <x-form.input name="salary_min" type="number" min="0" :value="$jobPosting->salary_min" />
        </x-form.field>

        <x-form.field name="salary_max" label="給与(上限・円)">
            <x-form.input name="salary_max" type="number" min="0" :value="$jobPosting->salary_max" />
        </x-form.field>
    </div>

    <x-form.field name="has_night_shift" label="夜勤">
        <label class="mt-1 flex items-center gap-2 text-sm">
            <input type="checkbox" name="has_night_shift" value="1"
                   @checked(old('has_night_shift', $jobPosting->has_night_shift))
                   class="rounded border-gray-300">
            夜勤あり
        </label>
    </x-form.field>

    <x-form.field name="qualification_ids" label="必要資格" help="複数選択できます。">
        <x-form.checkbox-group
            name="qualification_ids"
            :options="$qualifications->pluck('name', 'id')"
            :selected="$selectedQualificationIds" />
    </x-form.field>

    <x-form.field name="feature_ids" label="こだわり条件" help="複数選択できます。">
        <x-form.checkbox-group
            name="feature_ids"
            :options="$jobFeatures->pluck('name', 'id')"
            :selected="$selectedFeatureIds" />
    </x-form.field>

    <x-form.field name="working_hours" label="勤務時間">
        <x-form.textarea name="working_hours" :value="$jobPosting->working_hours" :rows="3" />
    </x-form.field>

    <x-form.field name="holidays" label="休日・休暇">
        <x-form.textarea name="holidays" :value="$jobPosting->holidays" :rows="3" />
    </x-form.field>

    <x-form.field name="benefits" label="待遇・福利厚生">
        <x-form.textarea name="benefits" :value="$jobPosting->benefits" :rows="3" />
    </x-form.field>

    <x-form.field name="description" label="仕事内容">
        <x-form.textarea name="description" :value="$jobPosting->description" :rows="6" />
    </x-form.field>

    <x-form.field name="allow_external_feed" label="外部媒体への配信"
                  help="Indeed・求人ボックス・スタンバイ等のアグリゲーション媒体にこの求人を配信します。運営者側の設定が OFF の場合は配信されません。">
        <label class="mt-1 flex items-center gap-2 text-sm">
            <input type="checkbox" name="allow_external_feed" value="1"
                   @checked(old('allow_external_feed', $jobPosting->allow_external_feed))
                   class="rounded border-gray-300">
            配信を許可する
        </label>
    </x-form.field>
</div>
