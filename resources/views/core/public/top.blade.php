{{--
    公開メディアのトップページ(コア)。
    T-10 で求人検索・注目求人などを実装する。ここではテーマ機構の動作確認用の最小構成。
--}}
@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-16">
        <p class="text-sm font-semibold" style="color: var(--theme-color)">{{ $site->site_name }}</p>

        <h1 class="mt-2 text-3xl font-bold">
            {{ $site->catch_copy ?: '介護・医療・福祉の求人を探す' }}
        </h1>

        <p class="mt-4 text-gray-600">
            このページはコア(<code>resources/views/core/public/top.blade.php</code>)です。
            テーマ側に同じパスのファイルを置くと、そちらが使われます。
        </p>

        <p class="mt-2 text-sm text-gray-500">
            現在のテーマ: <span data-testid="current-theme">{{ theme()->current() }}</span>
        </p>
    </section>
@endsection
