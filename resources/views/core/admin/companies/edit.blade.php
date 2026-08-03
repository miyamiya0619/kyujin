@extends('layouts.manage')

@section('title', $company->name.' の編集')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">{{ $company->name }} の編集</h1>

    <form method="POST" action="{{ route('admin.companies.update', $company) }}" enctype="multipart/form-data"
          class="mt-6 max-w-2xl rounded border border-[var(--border)] bg-[var(--surface)] p-6">
        @csrf
        @method('PUT')

        <x-form.field name="status" label="ステータス" required
                      help="停止中・契約終了にすると、この企業の求人は公開されなくなります。">
            <x-form.select
                name="status"
                :value="$company->status"
                :options="['active' => '掲載中', 'suspended' => '停止中', 'archived' => '契約終了']"
                placeholder="" />
        </x-form.field>

        <div class="mt-5">
            <x-company-form-fields :company="$company" :prefectures="$prefectures" :cities="$cities" />
        </div>

        <div class="mt-8 flex items-center gap-3">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                更新する
            </button>
            <a href="{{ route('admin.companies.show', $company) }}" class="text-sm text-[var(--ink-soft)] hover:underline">キャンセル</a>
        </div>
    </form>
@endsection
