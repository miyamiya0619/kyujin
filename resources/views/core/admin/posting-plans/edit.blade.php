@extends('layouts.manage')

@section('title', $postingPlan->name.' の編集')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <a href="{{ route('admin.posting-plans.index') }}" class="text-sm text-[var(--ink-soft)] hover:underline">&laquo; 掲載プラン一覧</a>

    <h1 class="mt-1 text-xl font-bold">{{ $postingPlan->name }} の編集</h1>

    <form method="POST" action="{{ route('admin.posting-plans.update', $postingPlan) }}"
          class="mt-6 max-w-2xl rounded border border-[var(--border)] bg-[var(--surface)] p-6">
        @csrf
        @method('PUT')

        <x-posting-plan-form-fields :posting-plan="$postingPlan" />

        <div class="mt-8 flex items-center gap-3">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                更新する
            </button>
            <a href="{{ route('admin.posting-plans.index') }}" class="text-sm text-[var(--ink-soft)] hover:underline">キャンセル</a>
        </div>
    </form>
@endsection
