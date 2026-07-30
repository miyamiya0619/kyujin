@extends('layouts.manage')

@section('title', 'ダッシュボード')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <h1 class="text-xl font-bold">ダッシュボード</h1>

    <p class="mt-4 text-sm text-gray-600">
        掲載中の求人数・未対応の応募件数・掲載プランの残枠は T-09 と T-13 で実装します。
    </p>
@endsection
