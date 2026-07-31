@extends('layouts.manage')

@section('title', '応募の横断確認')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">応募の横断確認</h1>
    <p class="mt-1 text-sm text-gray-600">全掲載企業の応募状況を確認できます(閲覧のみ)。</p>

    <form method="GET" action="{{ route('admin.applications.index') }}"
          class="mt-6 grid gap-4 rounded border border-gray-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-form.field name="company_id" label="掲載企業">
            <x-form.select name="company_id" :value="request('company_id')" :options="$companies->pluck('name', 'id')" />
        </x-form.field>

        <x-form.field name="status" label="選考ステータス">
            <x-form.select name="status" :value="request('status')" :options="$statusOptions" />
        </x-form.field>

        <x-form.field name="referrer_source" label="流入元">
            <x-form.select name="referrer_source" :value="request('referrer_source')" :options="[
                'direct' => '自社サイト',
                'indeed' => 'Indeed',
                'kyujinbox' => '求人ボックス',
                'stanby' => 'スタンバイ',
                'google' => 'Google しごと検索',
            ]" />
        </x-form.field>

        <div class="flex items-end gap-3">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                絞り込む
            </button>
            <a href="{{ route('admin.applications.index') }}" class="text-sm text-gray-600 hover:underline">条件をクリア</a>
        </div>
    </form>

    <div class="mt-6 overflow-x-auto rounded border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs text-gray-600">
                <tr>
                    <th class="px-4 py-3">掲載企業</th>
                    <th class="px-4 py-3">求人</th>
                    <th class="px-4 py-3">応募日</th>
                    <th class="px-4 py-3">流入元</th>
                    <th class="px-4 py-3">ステータス</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($applications as $application)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $application->company->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $application->jobPosting->title }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $application->applied_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $application->referrer_source }}</td>
                        <td class="px-4 py-3"><x-application-status-badge :status="$application->status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            該当する応募がありません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
