@extends('layouts.manage')

@section('title', 'ダッシュボード')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <h1 class="text-xl font-bold">ダッシュボード</h1>

    <div class="kpi-row mt-6">
        <div class="kpi">
            <p class="kpi-label">掲載中の求人</p>
            <p class="kpi-num">{{ $publishedJobPostingCount }} 件</p>
        </div>
        <a href="{{ route('company.applications.index') }}" class="kpi @if ($newApplicationsCount > 0) is-alert @endif">
            <p class="kpi-label">未対応の応募</p>
            <p class="kpi-num">{{ $newApplicationsCount }} 件</p>
        </a>
        <div class="kpi">
            <p class="kpi-label">求人の残枠</p>
            <p class="kpi-num">{{ $remainingJobPostingSlots === null ? '無制限' : "{$remainingJobPostingSlots} 件" }}</p>
        </div>
        <div class="kpi">
            <p class="kpi-label">事業所の残枠</p>
            <p class="kpi-num">{{ $remainingWorkplaceSlots === null ? '無制限' : "{$remainingWorkplaceSlots} 件" }}</p>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>新着応募者</h2>
            <a href="{{ route('company.applications.index') }}" class="text-xs hover:underline" style="color: var(--theme-color)">すべて見る &rarr;</a>
        </div>

        @forelse ($recentApplications as $application)
            <a href="{{ route('company.applications.show', $application) }}" class="flex items-center justify-between gap-3 p-4" style="border-bottom: 1px solid var(--border)">
                <div>
                    <p class="font-medium">{{ $application->jobPosting->title }}</p>
                    <p class="mt-1 text-xs" style="color: var(--muted)">応募日: {{ $application->applied_at->format('Y/m/d') }} ・ 流入元: {{ $application->referrer_source }}</p>
                </div>
                <x-application-status-badge :status="$application->status" />
            </a>
        @empty
            <p class="p-6 text-center text-sm" style="color: var(--muted)">まだ応募はありません。</p>
        @endforelse
    </div>

    <div class="panel">
        <div class="panel-head"><h2>掲載プラン</h2></div>
        <div class="p-4">
            @if ($currentPlan)
                <p class="text-sm">現在のプラン: <span class="font-medium">{{ $currentPlan->name }}</span></p>
            @else
                <p class="text-sm" style="color: var(--ink-soft)">
                    掲載プランが割り当てられていません(制限なくご利用いただけます)。
                </p>
            @endif
        </div>
    </div>
@endsection
