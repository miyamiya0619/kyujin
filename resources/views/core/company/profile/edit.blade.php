@extends('layouts.manage')

@section('title', '企業情報')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <h1 class="text-xl font-bold">企業情報</h1>

    <p class="mt-2 text-sm text-[var(--ink-soft)]">
        ここで登録した内容は、求人ページの企業紹介に表示されます。
    </p>

    <form method="POST" action="{{ route('company.profile.update') }}" enctype="multipart/form-data"
          class="mt-6 max-w-2xl rounded border border-[var(--border)] bg-[var(--surface)] p-6">
        @csrf
        @method('PUT')

        <x-company-form-fields :company="$company" :prefectures="$prefectures" :cities="$cities" />

        <div class="mt-8">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                更新する
            </button>
        </div>
    </form>
@endsection
