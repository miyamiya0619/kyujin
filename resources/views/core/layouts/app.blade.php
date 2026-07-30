{{--
    公開メディアサイトの共通レイアウト。

    このファイルはコア(全顧客共通)。顧客ごとに変えたい場合は
    resources/views/themes/{テーマ名}/layouts/app.blade.php に置けば上書きされる。

    $site はコアが全ビューに渡している(ThemeServiceProvider)。
    テーマ側でモデルを直接触る必要はない。
--}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', $site->site_name)</title>

    @if ($site->meta_description)
        <meta name="description" content="{{ $site->meta_description }}">
    @endif

    {{-- 顧客が標準テーマの色だけ変える場合はここが効く --}}
    <style>:root { --theme-color: {{ $site->theme_color }}; }</style>

    {{-- 共通の Tailwind。ビルドはローカルで行い public/build を配布する --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- テーマ固有の CSS。ビルド不要の素の CSS にしてある(理由は下記) --}}
    <link rel="stylesheet" href="{{ theme_asset('css/theme.css') }}">

    @stack('head')
</head>
<body class="min-h-screen flex flex-col bg-white text-gray-900">
    @include('layouts.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('layouts.footer')

    @stack('scripts')
</body>
</html>
