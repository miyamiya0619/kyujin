@extends('layouts.seeker')

@section('title', 'プロフィール編集')

@section('seeker-content')
        <h1 class="text-xl font-bold">プロフィール編集</h1>

        <form method="POST" action="{{ route('seeker.profile.update') }}"
              class="mt-6 rounded border border-[var(--border)] bg-[var(--surface)] p-6">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <x-form.field name="name" label="お名前" required>
                    <x-form.input name="name" :value="$jobSeeker->name" required />
                </x-form.field>

                <x-form.field name="name_kana" label="お名前(ふりがな)">
                    <x-form.input name="name_kana" :value="$jobSeeker->name_kana" />
                </x-form.field>

                <x-form.field name="tel" label="電話番号">
                    <x-form.input name="tel" type="tel" :value="$jobSeeker->tel" />
                </x-form.field>

                <x-form.field name="birthday" label="生年月日">
                    <x-form.input name="birthday" type="date" :value="$jobSeeker->birthday?->toDateString()" />
                </x-form.field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.field name="prefecture_id" label="都道府県">
                        <x-form.select
                            name="prefecture_id"
                            :value="$jobSeeker->prefecture_id"
                            :options="$prefectures->pluck('name', 'id')"
                            data-prefecture-select />
                    </x-form.field>

                    <x-form.field name="city_id" label="市区町村" help="都道府県を選ぶと候補が表示されます。">
                        <x-form.select
                            name="city_id"
                            :value="$jobSeeker->city_id"
                            :options="$cities->pluck('name', 'id')" />
                    </x-form.field>
                </div>

                <x-prefecture-city-select-script />

                <x-form.field name="qualification_ids" label="保有資格" help="複数選択できます。">
                    <x-form.checkbox-group
                        name="qualification_ids"
                        :options="$qualifications->pluck('name', 'id')"
                        :selected="$selectedQualificationIds" />
                </x-form.field>
            </div>

            <div class="mt-8">
                <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                        style="background-color: var(--theme-color)">
                    更新する
                </button>
            </div>
        </form>

        <div class="mt-8 rounded border border-[var(--border)] bg-[var(--surface)] p-6">
            <h2 class="text-sm font-semibold text-[var(--ink-soft)]">職務経歴</h2>

            <p class="mt-1 text-xs text-[var(--muted)]">
                健康状態・病歴・障害など機微な情報は記入しないでください。
            </p>

            <div id="experience-list" class="mt-4 space-y-4">
                @foreach ($jobSeeker->experiences as $experience)
                    <x-job-seeker-experience-item
                        :experience="$experience"
                        :is-first="$loop->first"
                        :is-last="$loop->last" />
                @endforeach
            </div>

            <x-experience-reorder-script />

            <details class="mt-4 rounded border border-[var(--border)] p-4">
                <summary class="cursor-pointer text-sm font-medium">職務経歴を追加する</summary>

                <form method="POST" action="{{ route('seeker.experiences.store') }}" class="mt-4 space-y-3">
                    @csrf

                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-form.field name="organization_name" label="事業所名" required>
                            <x-form.input name="organization_name" required />
                        </x-form.field>

                        <x-form.field name="job_title" label="職種">
                            <x-form.input name="job_title" />
                        </x-form.field>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-form.field name="started_on" label="在籍開始年月">
                            <x-form.input name="started_on" type="date" />
                        </x-form.field>

                        <x-form.field name="ended_on" label="在籍終了年月">
                            <x-form.input name="ended_on" type="date" />
                        </x-form.field>
                    </div>

                    <x-form.field name="description" label="業務内容">
                        <x-form.textarea name="description" :rows="3" />
                    </x-form.field>

                    <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                            style="background-color: var(--theme-color)">
                        追加する
                    </button>
                </form>
            </details>
        </div>
@endsection
