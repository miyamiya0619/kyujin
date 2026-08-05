@extends('layouts.app')

@section('title', '求人を探す'.($site->catch_copy ? " | {$site->catch_copy}" : ''))
@section('meta_description', "介護・医療・福祉の求人を都道府県・職種・雇用形態から検索できます。{$site->site_name}の求人一覧です。")

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-xl font-bold">求人を探す</h1>

        <div class="mt-4">
            <x-job-search-form
                :prefectures="$prefectures"
                :cities="$cities"
                :facility-types="$facilityTypes"
                :job-categories="$jobCategories"
                :employment-types="$employmentTypes"
                :job-features="$jobFeatures" />
        </div>

        <p class="mt-6 text-sm text-[var(--ink-soft)]">{{ $jobPostings->total() }} 件の求人が見つかりました。</p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @forelse ($jobPostings as $jobPosting)
                <x-job-posting-card :job-posting="$jobPosting" />
            @empty
                <p class="col-span-2 rounded border border-[var(--border)] bg-[var(--surface)] p-8 text-center text-[var(--muted)]">
                    条件に合う求人が見つかりませんでした。条件を変えてお試しください。
                </p>
            @endforelse
        </div>

        <div class="mt-6">{{ $jobPostings->onEachSide(1)->links() }}</div>
    </section>
@endsection
