@extends('layouts.manage')

@section('title', 'マスタ管理')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">マスタ管理</h1>
    <p class="mt-1 text-sm text-gray-600">
        有効/無効と並び順だけを変更できます。項目そのものの追加・削除はできません(製品が配布します)。
    </p>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($types as $type)
            <a href="{{ route('admin.masters.index', $type['slug']) }}"
               class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
                <p class="font-semibold">{{ $type['label'] }}</p>
                <p class="mt-1 text-sm text-gray-600">{{ $type['count'] }} 件</p>
            </a>
        @endforeach
    </div>
@endsection
