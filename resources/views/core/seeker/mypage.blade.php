@extends('layouts.app')

@section('title', 'マイページ')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full text-lg font-bold"
                     style="background-color: var(--brand-tint); color: var(--theme-color)">
                    {{ mb_substr(auth('seeker')->user()->name, 0, 1) }}
                </div>
                <div>
                    <p class="font-semibold">{{ auth('seeker')->user()->name }} さん</p>
                    <p class="text-xs" style="color: var(--muted)">こんにちは。今日も良い一日を。</p>
                </div>
            </div>

            <form method="POST" action="{{ route('seeker.logout') }}">
                @csrf
                <button type="submit" class="btn-ghost">ログアウト</button>
            </form>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <a href="{{ route('seeker.profile.edit') }}" class="card p-5 hover:shadow-md">
                <p class="font-semibold">プロフィール編集</p>
                <p class="mt-1 text-sm" style="color: var(--muted)">基本情報・保有資格・職務経歴</p>
            </a>
            <a href="{{ route('public.jobs.index') }}" class="card p-5 hover:shadow-md">
                <p class="font-semibold">求人を探す</p>
                <p class="mt-1 text-sm" style="color: var(--muted)">条件を指定して求人を検索する</p>
            </a>
        </div>

        <div class="mt-10">
            <h2 class="font-display text-lg font-semibold">応募履歴</h2>

            @if ($applications->isEmpty())
                <p class="card mt-3 p-6 text-center text-sm" style="color: var(--muted)">まだ応募した求人はありません。</p>
            @else
                <div class="mt-3 space-y-3">
                    @foreach ($applications as $application)
                        <div class="card flex items-center justify-between gap-3 p-4">
                            <div>
                                <p class="text-sm font-semibold">{{ $application->jobPosting->title }}</p>
                                <p class="mt-1 text-xs" style="color: var(--muted)">{{ $application->jobPosting->company->name }}</p>
                                <p class="mt-2 text-xs" style="color: var(--muted)">応募日: {{ $application->applied_at->format('Y/m/d') }}</p>
                            </div>
                            <x-application-status-badge :status="$application->status" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-12 border-t pt-6" style="border-color: var(--border)">
            <p class="text-xs" style="color: var(--muted)">
                退会するとプロフィール・保有資格・職務経歴は削除されます。ただし、
                既に応募した求人については、応募時点の内容が選考記録として掲載企業側に残ります。
            </p>
            <form method="POST" action="{{ route('seeker.account.destroy') }}"
                  onsubmit="return confirm('退会するとプロフィール情報が削除されます。よろしいですか?(応募済みの選考記録は掲載企業側に残ります)')"
                  class="mt-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs hover:underline" style="color: var(--danger)">退会する</button>
            </form>
        </div>
    </section>
@endsection
