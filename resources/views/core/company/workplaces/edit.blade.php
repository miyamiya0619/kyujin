@extends('layouts.manage')

@section('title', $workplace->name.' の編集')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <a href="{{ route('company.workplaces.index') }}" class="text-sm text-[var(--ink-soft)] hover:underline">&laquo; 事業所一覧</a>

    <h1 class="mt-1 text-xl font-bold">{{ $workplace->name }} の編集</h1>

    <form method="POST" action="{{ route('company.workplaces.update', $workplace) }}" enctype="multipart/form-data"
          class="mt-6 max-w-2xl rounded border border-[var(--border)] bg-[var(--surface)] p-6">
        @csrf
        @method('PUT')

        <x-workplace-form-fields
            :workplace="$workplace"
            :facility-types="$facilityTypes"
            :prefectures="$prefectures"
            :cities="$cities" />

        <div class="mt-8 flex items-center gap-3">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                更新する
            </button>
            <a href="{{ route('company.workplaces.index') }}" class="text-sm text-[var(--ink-soft)] hover:underline">キャンセル</a>
        </div>
    </form>
@endsection
