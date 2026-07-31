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

    @php($metaDescription = trim($__env->yieldContent('meta_description', $site->meta_description ?? '')))
    @if ($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif

    <link rel="canonical" href="{{ url()->current() }}">

    {{-- OGP。個別ページは @section('og_title') 等で上書きできる --}}
    <meta property="og:site_name" content="{{ $site->site_name }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('og_title', $__env->yieldContent('title', $site->site_name))">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($site->key_visual_path)
        <meta property="og:image" content="{{ \App\Services\ImageUploadService::url($site->key_visual_path) }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">

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
