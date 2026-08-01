@extends('layouts.app')

@section('title', 'マイページ')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">マイページ</h1>

            <form method="POST" action="{{ route('seeker.logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 hover:underline">ログアウト</button>
            </form>
        </div>

        <p class="mt-6 text-sm text-gray-600">
            {{ auth('seeker')->user()->name }} さん、こんにちは。
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <a href="{{ route('seeker.profile.edit') }}"
               class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
                <p class="font-semibold">プロフィール編集</p>
                <p class="mt-1 text-sm text-gray-600">基本情報・保有資格・職務経歴</p>
            </a>
        </div>

        <div class="mt-8">
            <h2 class="text-sm font-semibold text-gray-700">応募履歴</h2>

            @if ($applications->isEmpty())
                <p class="mt-2 text-sm text-gray-500">まだ応募した求人はありません。</p>
            @else
                <div class="mt-3 space-y-3">
                    @foreach ($applications as $application)
                        <div class="rounded border border-gray-200 bg-white p-4">
                            <p class="text-sm font-semibold">{{ $application->jobPosting->title }}</p>
                            <p class="mt-1 text-xs text-gray-600">{{ $application->jobPosting->company->name }}</p>
                            <p class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                <span>応募日: {{ $application->applied_at->format('Y/m/d') }}</span>
                                <span class="rounded bg-gray-100 px-2 py-0.5">{{ $application->statusLabel() }}</span>
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-10 border-t border-gray-100 pt-6">
            <p class="text-xs text-gray-500">
                退会するとプロフィール・保有資格・職務経歴は削除されます。ただし、
                既に応募した求人については、応募時点の内容が選考記録として掲載企業側に残ります。
            </p>
            <form method="POST" action="{{ route('seeker.account.destroy') }}"
                  onsubmit="return confirm('退会するとプロフィール情報が削除されます。よろしいですか?(応募済みの選考記録は掲載企業側に残ります)')"
                  class="mt-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-600 hover:underline">退会する</button>
            </form>
        </div>
    </section>
@endsection
