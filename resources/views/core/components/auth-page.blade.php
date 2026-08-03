{{--
    認証画面(ログイン・パスワード再設定)の共通シェル。
    運営者・掲載企業・求職者の 3 つで使い回す。

    テーマで差し替えたい場合は
    resources/views/themes/{テーマ名}/components/auth-page.blade.php に置く。
--}}
@props(['title', 'backUrl' => null, 'backLabel' => null])

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ $site->site_name }}</title>
    <meta name="robots" content="noindex,nofollow">
    <style>:root { --theme-color: {{ $site->theme_color }}; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ theme_asset('css/theme.css') }}">
</head>
<body class="flex min-h-screen items-center justify-center px-4 py-12" style="background-color: var(--bg); color: var(--ink)">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            <p class="font-display text-lg font-bold" style="color: var(--theme-color)">{{ $site->site_name }}</p>
            <h1 class="mt-1 text-sm" style="color: var(--ink-soft)">{{ $title }}</h1>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded border px-4 py-3 text-sm"
                 style="border-color: var(--success); background-color: var(--success-bg); color: var(--success)">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border px-4 py-3 text-sm"
                 style="border-color: var(--danger); background-color: var(--danger-bg); color: var(--danger)">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card p-6" style="box-shadow: 0 1px 2px rgba(30,42,36,.06), 0 8px 24px -12px rgba(30,42,36,.18)">
            {{ $slot }}
        </div>

        @if ($backUrl)
            <p class="mt-4 text-center text-sm">
                <a href="{{ $backUrl }}" class="hover:underline" style="color: var(--ink-soft)">{{ $backLabel }}</a>
            </p>
        @endif
    </div>
</body>
</html>
