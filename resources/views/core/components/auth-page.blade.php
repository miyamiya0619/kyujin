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
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            <p class="text-lg font-bold" style="color: var(--theme-color)">{{ $site->site_name }}</p>
            <h1 class="mt-1 text-sm text-gray-600">{{ $title }}</h1>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
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

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            {{ $slot }}
        </div>

        @if ($backUrl)
            <p class="mt-4 text-center text-sm">
                <a href="{{ $backUrl }}" class="text-gray-600 hover:underline">{{ $backLabel }}</a>
            </p>
        @endif
    </div>
</body>
</html>
