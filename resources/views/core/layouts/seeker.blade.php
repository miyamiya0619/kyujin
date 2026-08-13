{{--
    ログイン中の求職者向けページの共通シェル。

    layouts.app(公開サイトのヘッダー・フッター)を土台に、左サイドナビ + 右コンテンツの
    アプリシェル型を重ねる。マイページ・プロフィール編集など、ログイン後の画面はこれを
    @extends し、本体は @section('seeker-content') に書く(@section('content') は
    このレイアウト自身が使っているため)。
--}}
@extends('layouts.app')

@section('content')
    <section class="mx-auto px-4 py-10">
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
                    <a href="{{ route('seeker.mypage') }}"
                       @class(['is-current' => request()->routeIs('seeker.mypage')])>応募履歴</a>
                    <a href="{{ route('seeker.profile.edit') }}"
                       @class(['is-current' => request()->routeIs('seeker.profile.*')])>プロフィール編集</a>
                    <a href="{{ route('public.jobs.index') }}">求人を探す</a>
                </nav>

                <div class="seeker-account">
                    <form method="POST" action="{{ route('seeker.logout') }}">
                        @csrf
                        <button type="submit">ログアウト</button>
                    </form>
                    <a href="{{ route('seeker.account.confirm') }}" class="is-danger">退会する</a>
                </div>
            </aside>

            <div class="seeker-main">
                @yield('seeker-content')
            </div>
        </div>
    </section>
@endsection
