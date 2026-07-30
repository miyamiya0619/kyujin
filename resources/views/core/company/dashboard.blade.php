@extends('layouts.manage')

@section('title', 'ダッシュボード')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <h1 class="text-xl font-bold">ダッシュボード</h1>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('company.profile.edit') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">企業情報</p>
            <p class="mt-1 text-sm text-gray-600">求人ページに表示される自社の紹介</p>
        </a>

        <a href="{{ route('company.workplaces.index') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">事業所</p>
            <p class="mt-1 text-sm text-gray-600">特養・デイサービスなどの拠点情報</p>
        </a>

        <a href="{{ route('company.job-postings.index') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">求人</p>
            <p class="mt-1 text-sm text-gray-600">求人の登録・編集・複製</p>
        </a>
    </div>

    <p class="mt-6 text-sm text-gray-500">
        掲載中の求人数・未対応の応募件数・掲載プランの残枠は T-09 と T-13 で実装します。
    </p>
@endsection
