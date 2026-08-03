{{--
    管理画面(運営者・掲載企業)の共通レイアウト。

    公開サイトと違い検索エンジンに拾わせないため noindex を付ける。
    左サイドナビの項目はログイン中のガード(admin / company)で出し分ける。
    差分はそれだけなので 1 つのレイアウトで両方をまかなう。
--}}
@props([])
@php
    // 呼び出し側が @section で指定する

    $isAdmin = auth('admin')->check();

    $navItems = $isAdmin
        ? [
            ['label' => 'ダッシュボード', 'route' => 'admin.dashboard'],
            ['label' => '審査待ち', 'route' => 'admin.reviews.index', 'badge' => $pendingReviewCount ?? null],
            ['label' => '掲載企業', 'route' => 'admin.companies.index'],
            ['label' => '掲載プラン', 'route' => 'admin.posting-plans.index'],
            ['label' => '応募の横断確認', 'route' => 'admin.applications.index'],
            ['label' => '媒体別の効果', 'route' => 'admin.feeds.index'],
            ['label' => 'サイト設定', 'route' => 'admin.site-settings.edit'],
            ['label' => 'マスタ管理', 'route' => 'admin.masters.home'],
            ['label' => '監査ログ', 'route' => 'admin.audit-logs.index'],
        ]
        : [
            ['label' => 'ダッシュボード', 'route' => 'company.dashboard'],
            ['label' => '企業情報', 'route' => 'company.profile.edit'],
            ['label' => '事業所', 'route' => 'company.workplaces.index'],
            ['label' => '求人', 'route' => 'company.job-postings.index'],
            ['label' => '応募者', 'route' => 'company.applications.index', 'badge' => $unhandledApplicationCount ?? null],
        ];
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
    <link rel="stylesheet" href="{{ theme_asset('css/theme.css') }}">
</head>
<body style="background-color: var(--bg); color: var(--ink)">
    <header class="border-b" style="border-color: var(--border); background-color: var(--surface)">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-baseline gap-3">
                <span class="font-display font-bold" style="color: var(--theme-color)">{{ $site->site_name }}</span>
                <span class="text-sm" style="color: var(--muted)">@yield('context')</span>
            </div>

            <div class="flex items-center gap-4 text-sm" style="color: var(--ink-soft)">
                <span>@yield('user-name')</span>
                <form method="POST" action="@yield('logout-url')">
                    @csrf
                    <button type="submit" class="hover:underline">ログアウト</button>
                </form>
            </div>
        </div>
    </header>

    <div class="admin-shell">
        <aside class="admin-side">
            <div class="brand">{{ $isAdmin ? $site->site_name.' 運営管理' : (auth('company')->user()->company->name ?? '') }}</div>
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}" @class(['is-current' => request()->routeIs($item['route'].'*')])>
                    {{ $item['label'] }}
                    @if (! empty($item['badge']))
                        <span class="badge">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </aside>

        <main class="admin-main">
            @if (session('status'))
                <div class="mb-4 rounded border px-4 py-3 text-sm"
                     style="border-color: var(--success); background-color: var(--success-bg); color: var(--success)">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded border px-4 py-3 text-sm"
                     style="border-color: var(--danger); background-color: var(--danger-bg); color: var(--danger)">
                    {{ session('error') }}
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

            @yield('content')
        </main>
    </div>
</body>
</html>
