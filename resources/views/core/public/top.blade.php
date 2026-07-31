@extends('layouts.app')

@section('title', $site->catch_copy ? "{$site->site_name} | {$site->catch_copy}" : $site->site_name)
@section('meta_description', $site->meta_description ?: "介護・医療・福祉の求人情報サイト「{$site->site_name}」。地域・職種・雇用形態から求人を検索できます。")

@section('content')
    <section class="border-b border-gray-100 bg-gray-50">
        <div class="mx-auto max-w-5xl px-4 py-12">
            <p class="text-sm font-semibold" style="color: var(--theme-color)">{{ $site->site_name }}</p>

            <h1 class="mt-2 text-2xl font-bold sm:text-3xl">
                {{ $site->catch_copy ?: '介護・医療・福祉の求人を探す' }}
            </h1>

            <p class="mt-2 text-sm text-gray-600">{{ number_format($totalJobPostingCount) }} 件の求人を掲載中</p>

            <div class="mt-6">
                <x-job-search-form :prefectures="$prefectures" :cities="collect()" :compact="true" />
            </div>
        </div>
    </section>

    @if ($featuredJobPostings->isNotEmpty())
        <section class="mx-auto max-w-5xl px-4 py-10">
            <h2 class="text-lg font-bold">おすすめの求人</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredJobPostings as $jobPosting)
                    <x-job-posting-card :job-posting="$jobPosting" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-5xl px-4 py-10">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold">新着求人</h2>
            <a href="{{ route('public.jobs.index') }}" class="text-sm hover:underline" style="color: var(--theme-color)">
                すべての求人を見る &raquo;
            </a>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($newJobPostings as $jobPosting)
                <x-job-posting-card :job-posting="$jobPosting" />
            @empty
                <p class="col-span-full rounded border border-gray-200 bg-white p-8 text-center text-gray-500">
                    現在募集中の求人はありません。
                </p>
            @endforelse
        </div>
    </section>
@endsection
