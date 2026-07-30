@extends('layouts.manage')

@section('title', '掲載企業の追加')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">掲載企業の追加</h1>

    <form method="POST" action="{{ route('admin.companies.store') }}" enctype="multipart/form-data"
          class="mt-6 max-w-2xl rounded border border-gray-200 bg-white p-6">
        @csrf
        <input type="hidden" name="status" value="active">

        <x-company-form-fields :company="$company" :prefectures="$prefectures" :cities="$cities" />

        <div class="mt-8 flex items-center gap-3">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                登録する
            </button>
            <a href="{{ route('admin.companies.index') }}" class="text-sm text-gray-600 hover:underline">キャンセル</a>
        </div>
    </form>
@endsection
