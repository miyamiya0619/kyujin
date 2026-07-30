{{--
    管理画面(運営者・掲載企業)の共通レイアウト。

    公開サイトと違い検索エンジンに拾わせないため noindex を付ける。
    差分はヘッダーの表示名とログアウト先だけなので 1 つのレイアウトで両方をまかなう。
--}}
@props([])
@php
    // 呼び出し側が @section で指定する
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') | {{ $site->site_name }}</title>
    <style>:root { --theme-color: {{ $site->theme_color }}; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <div class="flex items-baseline gap-3">
                <span class="font-bold" style="color: var(--theme-color)">{{ $site->site_name }}</span>
                <span class="text-sm text-gray-500">@yield('context')</span>
            </div>

            <div class="flex items-center gap-4 text-sm">
                <span class="text-gray-600">@yield('user-name')</span>
                <form method="POST" action="@yield('logout-url')">
                    @csrf
                    <button type="submit" class="text-gray-600 hover:underline">ログアウト</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('status'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
