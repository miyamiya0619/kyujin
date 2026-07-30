@extends('layouts.manage')

@section('title', '事業所の追加')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <a href="{{ route('company.workplaces.index') }}" class="text-sm text-gray-600 hover:underline">&laquo; 事業所一覧</a>

    <h1 class="mt-1 text-xl font-bold">事業所の追加</h1>

    <form method="POST" action="{{ route('company.workplaces.store') }}" enctype="multipart/form-data"
          class="mt-6 max-w-2xl rounded border border-gray-200 bg-white p-6">
        @csrf

        <x-workplace-form-fields
            :workplace="$workplace"
            :facility-types="$facilityTypes"
            :prefectures="$prefectures"
            :cities="$cities" />

        <div class="mt-8 flex items-center gap-3">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                登録する
            </button>
            <a href="{{ route('company.workplaces.index') }}" class="text-sm text-gray-600 hover:underline">キャンセル</a>
        </div>
    </form>
@endsection
