@extends('layouts.manage')

@section('title', 'ダッシュボード')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">ダッシュボード</h1>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('admin.reviews.index') }}"
           class="rounded border border-[var(--border)] bg-[var(--surface)] p-5 hover:border-[var(--muted)]">
            <p class="font-semibold">審査待ち</p>
            <p class="mt-1 text-2xl font-bold" style="color: var(--theme-color)">{{ $pendingCount }} 件</p>
        </a>

        <div class="rounded border border-[var(--border)] bg-[var(--surface)] p-5">
            <p class="font-semibold">掲載中求人数</p>
            <p class="mt-1 text-2xl font-bold" style="color: var(--theme-color)">{{ $publishedCount }} 件</p>
        </div>

        <div class="rounded border border-[var(--border)] bg-[var(--surface)] p-5">
            <p class="font-semibold">今月の応募数</p>
            <p class="mt-1 text-2xl font-bold" style="color: var(--theme-color)">{{ $monthlyApplicationsCount }} 件</p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('admin.companies.index') }}"
           class="rounded border border-[var(--border)] bg-[var(--surface)] p-5 hover:border-[var(--muted)]">
            <p class="font-semibold">掲載企業</p>
            <p class="mt-1 text-sm text-[var(--ink-soft)]">企業の登録と担当者アカウントの発行</p>
        </a>

        <a href="{{ route('admin.posting-plans.index') }}"
           class="rounded border border-[var(--border)] bg-[var(--surface)] p-5 hover:border-[var(--muted)]">
            <p class="font-semibold">掲載プラン</p>
            <p class="mt-1 text-sm text-[var(--ink-soft)]">プランの定義と掲載企業への割当</p>
        </a>

        <a href="{{ route('admin.applications.index') }}"
           class="rounded border border-[var(--border)] bg-[var(--surface)] p-5 hover:border-[var(--muted)]">
            <p class="font-semibold">応募の横断確認</p>
            <p class="mt-1 text-sm text-[var(--ink-soft)]">全掲載企業の応募状況を確認</p>
        </a>

        <a href="{{ route('admin.feeds.index') }}"
           class="rounded border border-[var(--border)] bg-[var(--surface)] p-5 hover:border-[var(--muted)]">
            <p class="font-semibold">媒体別の効果</p>
            <p class="mt-1 text-sm text-[var(--ink-soft)]">Indeed・求人ボックス等の配信状況と応募数</p>
        </a>

        <a href="{{ route('admin.site-settings.edit') }}"
           class="rounded border border-[var(--border)] bg-[var(--surface)] p-5 hover:border-[var(--muted)]">
            <p class="font-semibold">サイト設定</p>
            <p class="mt-1 text-sm text-[var(--ink-soft)]">メディア名・ロゴ・テーマ・機能のオンオフ</p>
        </a>

        <a href="{{ route('admin.masters.home') }}"
           class="rounded border border-[var(--border)] bg-[var(--surface)] p-5 hover:border-[var(--muted)]">
            <p class="font-semibold">マスタ管理</p>
            <p class="mt-1 text-sm text-[var(--ink-soft)]">資格・施設形態・職種などの有効/無効と並び順</p>
        </a>

        <a href="{{ route('admin.audit-logs.index') }}"
           class="rounded border border-[var(--border)] bg-[var(--surface)] p-5 hover:border-[var(--muted)]">
            <p class="font-semibold">監査ログ</p>
            <p class="mt-1 text-sm text-[var(--ink-soft)]">操作者・期間・対象で絞り込んで確認</p>
        </a>
    </div>
@endsection
