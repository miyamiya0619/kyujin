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

        <p class="mt-6 text-sm text-gray-500">
            応募履歴は T-12 で実装します。
        </p>

        <div class="mt-10 border-t border-gray-100 pt-6">
            <form method="POST" action="{{ route('seeker.account.destroy') }}"
                  onsubmit="return confirm('退会するとプロフィール情報が削除されます。よろしいですか?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-600 hover:underline">退会する</button>
            </form>
        </div>
    </section>
@endsection
