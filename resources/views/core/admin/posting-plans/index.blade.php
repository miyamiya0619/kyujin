@extends('layouts.manage')

@section('title', '掲載プラン')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">掲載プラン</h1>

        <a href="{{ route('admin.posting-plans.create') }}"
           class="rounded px-4 py-2 text-sm font-semibold text-white"
           style="background-color: var(--theme-color)">
            プランを追加
        </a>
    </div>

    <div class="mt-6 overflow-x-auto rounded border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs text-gray-600">
                <tr>
                    <th class="px-4 py-3">プラン名</th>
                    <th class="px-4 py-3">同時掲載件数</th>
                    <th class="px-4 py-3">掲載期間</th>
                    <th class="px-4 py-3">上位表示</th>
                    <th class="px-4 py-3">月額</th>
                    <th class="px-4 py-3">割当企業数</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($postingPlans as $plan)
                    <tr class="{{ $plan->is_enabled ? '' : 'text-gray-400' }}">
                        <td class="px-4 py-3 font-medium">
                            {{ $plan->name }}
                            @unless ($plan->is_enabled)
                                <span class="ml-1 text-xs">(無効)</span>
                            @endunless
                        </td>
                        <td class="px-4 py-3">{{ $plan->max_job_postings ?? '無制限' }}</td>
                        <td class="px-4 py-3">{{ $plan->posting_duration_days ? "{$plan->posting_duration_days}日" : '無期限' }}</td>
                        <td class="px-4 py-3">{{ $plan->is_featured ? 'あり' : 'なし' }}</td>
                        <td class="px-4 py-3">{{ $plan->monthly_price !== null ? number_format($plan->monthly_price).'円' : '-' }}</td>
                        <td class="px-4 py-3">{{ $plan->assignments_count }} 社</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.posting-plans.edit', $plan) }}" class="text-gray-600 hover:underline">編集</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            掲載プランがまだ登録されていません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
