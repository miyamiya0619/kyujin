@extends('layouts.app')

@section('title', 'マイページ')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10">
        <div class="seeker-shell">
            <aside class="seeker-side">
                <div class="seeker-who">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                         style="background-color: var(--brand-tint); color: var(--theme-color)">
                        {{ mb_substr(auth('seeker')->user()->name, 0, 1) }}
                    </div>
                    <p class="min-w-0 truncate text-sm font-semibold">{{ auth('seeker')->user()->name }} さん</p>
                </div>

                <nav>
                    <a href="{{ route('seeker.mypage') }}" class="is-current">応募履歴</a>
                    <a href="{{ route('seeker.profile.edit') }}">プロフィール編集</a>
                    <a href="{{ route('public.jobs.index') }}">求人を探す</a>
                </nav>

                <div class="seeker-account">
                    <form method="POST" action="{{ route('seeker.logout') }}">
                        @csrf
                        <button type="submit">ログアウト</button>
                    </form>
                    <form method="POST" action="{{ route('seeker.account.destroy') }}"
                          onsubmit="return confirm('退会するとプロフィール・保有資格・職務経歴が削除されます。よろしいですか?(応募済みの求人については、応募時点の内容が選考記録として掲載企業側に残ります)')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="is-danger">退会する</button>
                    </form>
                </div>
            </aside>

            <div class="seeker-main">
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
            </div>
        </div>
    </section>
@endsection
