@extends('layouts.app')

@section('title', '退会する')

@section('content')
    <section class="mx-auto max-w-md px-4 py-12">
        <a href="{{ route('seeker.mypage') }}" class="text-sm hover:underline" style="color: var(--ink-soft)">
            &laquo; マイページに戻る
        </a>

        <h1 class="mt-3 text-xl font-bold">退会する</h1>

        <p class="mt-3 text-sm leading-relaxed" style="color: var(--ink-soft)">
            退会すると、プロフィール・保有資格・職務経歴のデータは削除されます。
            ただし、既に応募した求人については、応募時点の内容が選考記録として
            掲載企業側に残ります。<strong>この操作は取り消せません。</strong>
        </p>

        <div class="mt-6 card p-6">
            <p class="text-xs font-semibold" style="color: var(--muted)">退会するアカウント</p>
            <p class="mt-2 rounded px-3 py-2 text-sm" style="background-color: var(--bg); color: var(--ink)">
                {{ auth('seeker')->user()->email }}
            </p>

            <form method="POST" action="{{ route('seeker.account.destroy') }}" class="mt-6">
                @csrf
                @method('DELETE')

                <label class="flex items-start gap-2 text-sm" style="color: var(--ink-soft)">
                    <input type="checkbox" required class="mt-0.5">
                    <span>上記の内容をすべて確認しました</span>
                </label>

                <div class="mt-5 flex flex-col gap-3">
                    <button type="submit" class="btn-danger w-full">退会する</button>
                    <a href="{{ route('seeker.mypage') }}" class="btn-ghost w-full">キャンセル</a>
                </div>
            </form>
        </div>
    </section>
@endsection
