@extends('layouts.manage')

@section('title', 'ダッシュボード')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">ダッシュボード</h1>

    <div class="kpi-row mt-6">
        <a href="{{ route('admin.reviews.index') }}" class="kpi @if ($pendingCount > 0) is-alert @endif">
            <p class="kpi-label">審査待ち</p>
            <p class="kpi-num">{{ $pendingCount }} 件</p>
        </a>
        <div class="kpi">
            <p class="kpi-label">掲載中求人数</p>
            <p class="kpi-num">{{ $publishedCount }} 件</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">今月の応募数</p>
            <p class="kpi-num">{{ $monthlyApplicationsCount }} 件</p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="panel">
                <div class="panel-head">
                    <h2>審査待ち一覧</h2>
                    <a href="{{ route('admin.reviews.index') }}" class="text-xs hover:underline" style="color: var(--theme-color)">すべて見る &rarr;</a>
                </div>

                @forelse ($pendingReviews as $jobPosting)
                    <div class="p-4" style="border-bottom: 1px solid var(--border)">
                        <p class="text-xs" style="color: var(--muted)">{{ $jobPosting->company->name }} / {{ $jobPosting->workplace->name }}</p>
                        <p class="mt-1 font-semibold">{{ $jobPosting->title }}</p>
                        <div class="mt-3 flex gap-2">
                            <form method="POST" action="{{ route('admin.job-postings.approve', $jobPosting) }}">
                                @csrf
                                <button type="submit" class="rounded px-3 py-1.5 text-xs font-semibold text-white" style="background-color: var(--success)">承認して公開する</button>
                            </form>
                            <a href="{{ route('admin.companies.job-postings.edit', [$jobPosting->company, $jobPosting]) }}" class="btn-ghost" style="padding: 0.35rem 0.9rem; font-size: 12.5px;">内容を確認</a>
                        </div>
                    </div>
                @empty
                    <p class="p-6 text-center text-sm" style="color: var(--muted)">審査待ちの求人はありません。</p>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>媒体別の応募割合</h2></div>
            <div class="p-4">
                @foreach ($mediaNames as $source => $label)
                    @php($count = $applicationCounts[$source] ?? 0)
                    <div class="mini-bar-row">
                        <span class="mini-bar-name">{{ $label }}</span>
                        <div class="mini-bar-track"><div class="mini-bar-fill" style="width: {{ (int) ($count / $maxApplicationCount * 100) }}%"></div></div>
                        <span class="mini-bar-val">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
