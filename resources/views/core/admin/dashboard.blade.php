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
    </div>

    <p class="mt-6 text-sm text-gray-500">
        掲載中求人数・応募数・媒体別流入は T-16 で実装します。
    </p>
@endsection
