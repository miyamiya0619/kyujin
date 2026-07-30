@extends('layouts.manage')

@section('title', '掲載プランの追加')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <a href="{{ route('admin.posting-plans.index') }}" class="text-sm text-gray-600 hover:underline">&laquo; 掲載プラン一覧</a>

    <h1 class="mt-1 text-xl font-bold">掲載プランの追加</h1>

    <form method="POST" action="{{ route('admin.posting-plans.store') }}"
          class="mt-6 max-w-2xl rounded border border-gray-200 bg-white p-6">
        @csrf

        <x-posting-plan-form-fields :posting-plan="$postingPlan" />

        <div class="mt-8 flex items-center gap-3">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                作成する
            </button>
            <a href="{{ route('admin.posting-plans.index') }}" class="text-sm text-gray-600 hover:underline">キャンセル</a>
        </div>
    </form>
@endsection
