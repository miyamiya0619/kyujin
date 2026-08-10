@extends('layouts.seeker')

@section('title', 'マイページ')

@section('seeker-content')
    <div class="seeker-stats">
        <span class="count-badge">応募総数 <span class="count-badge-num tabular-nums">{{ $stats['total'] }}</span> 件</span>
        <span class="count-badge">選考中 <span class="count-badge-num tabular-nums">{{ $stats['in_progress'] }}</span> 件</span>
        <span class="count-badge">内定 <span class="count-badge-num tabular-nums">{{ $stats['offer'] }}</span> 件</span>
    </div>

    <div class="seeker-panel">
        <div class="seeker-panel-head">応募履歴</div>

        @if ($applications->isEmpty())
            <p class="p-6 text-center text-sm" style="color: var(--muted)">まだ応募した求人はありません。</p>
        @else
            @foreach ($applications as $application)
                <div class="seeker-row">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ $application->jobPosting->title }}</p>
                        <p class="seeker-row-company truncate">{{ $application->jobPosting->company->name }}</p>
                    </div>
                    <x-application-status-badge :status="$application->status" />
                    <p class="seeker-row-date tabular-nums">{{ $application->applied_at->format('Y/m/d') }}</p>
                </div>
            @endforeach
        @endif
    </div>
@endsection
