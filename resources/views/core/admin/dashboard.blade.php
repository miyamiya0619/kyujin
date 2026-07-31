@extends('layouts.manage')

@section('title', 'ダッシュボード')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">ダッシュボード</h1>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('admin.reviews.index') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">審査待ち</p>
            <p class="mt-1 text-2xl font-bold" style="color: var(--theme-color)">{{ $pendingCount }} 件</p>
        </a>

        <a href="{{ route('admin.companies.index') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">掲載企業</p>
            <p class="mt-1 text-sm text-gray-600">企業の登録と担当者アカウントの発行</p>
        </a>

        <a href="{{ route('admin.posting-plans.index') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">掲載プラン</p>
            <p class="mt-1 text-sm text-gray-600">プランの定義と掲載企業への割当</p>
        </a>

        <a href="{{ route('admin.applications.index') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">応募の横断確認</p>
            <p class="mt-1 text-sm text-gray-600">全掲載企業の応募状況を確認</p>
        </a>

        <a href="{{ route('admin.feeds.index') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">媒体別の効果</p>
            <p class="mt-1 text-sm text-gray-600">Indeed・求人ボックス等の配信状況と応募数</p>
        </a>
    </div>

    <p class="mt-6 text-sm text-gray-500">
        掲載中求人数の集計は T-16 で実装します。
    </p>
@endsection
