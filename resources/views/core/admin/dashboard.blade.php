@extends('layouts.manage')

@section('title', 'ダッシュボード')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">ダッシュボード</h1>

    <p class="mt-4 text-sm text-gray-600">
        審査待ち件数・掲載中求人数・応募数・媒体別流入は T-16 で実装します。
    </p>
@endsection
