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

        <p class="mt-4 text-sm text-gray-500">
            プロフィール・保有資格・職務経歴は T-11、応募履歴は T-12 で実装します。
        </p>
    </section>
@endsection
