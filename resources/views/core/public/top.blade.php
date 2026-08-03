@extends('layouts.app')

@section('title', $site->catch_copy ? "{$site->site_name} | {$site->catch_copy}" : $site->site_name)
@section('meta_description', $site->meta_description ?: "介護・医療・福祉の求人情報サイト「{$site->site_name}」。地域・職種・雇用形態から求人を検索できます。")

@section('content')
    <section class="border-b" style="border-color: var(--border); background: radial-gradient(720px 420px at 82% -10%, var(--brand-tint), transparent 60%), var(--bg)">
        <div class="mx-auto max-w-5xl px-4 py-16 text-center">
            <p class="text-xs font-bold uppercase tracking-widest" style="color: var(--theme-color)">{{ $site->site_name }}</p>

            <h1 class="font-display mt-3 text-3xl font-semibold leading-relaxed sm:text-4xl">
                {{ $site->catch_copy ?: '介護・医療・福祉の求人を探す' }}
            </h1>

            <p class="mt-3 text-sm" style="color: var(--ink-soft)">{{ number_format($totalJobPostingCount) }} 件の求人を掲載中</p>

            <div class="mx-auto mt-8 max-w-3xl text-left">
                <x-job-search-form :prefectures="$prefectures" :cities="collect()" :compact="true" />
            </div>
        </div>
    </section>

    @if ($featuredJobPostings->isNotEmpty())
        <section class="mx-auto max-w-5xl px-4 py-12">
            <div class="flex items-baseline justify-between">
                <h2 class="font-display text-xl font-semibold">おすすめの求人</h2>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredJobPostings as $jobPosting)
                    <x-job-posting-card :job-posting="$jobPosting" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-5xl px-4 py-12">
        <div class="flex items-baseline justify-between">
            <h2 class="font-display text-xl font-semibold">新着求人</h2>
            <a href="{{ route('public.jobs.index') }}" class="text-sm hover:underline" style="color: var(--theme-color)">
                すべての求人を見る &raquo;
            </a>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($newJobPostings as $jobPosting)
                <x-job-posting-card :job-posting="$jobPosting" />
            @empty
                <p class="card col-span-full p-8 text-center" style="color: var(--muted)">
                    現在募集中の求人はありません。
                </p>
            @endforelse
        </div>
    </section>
@endsection
