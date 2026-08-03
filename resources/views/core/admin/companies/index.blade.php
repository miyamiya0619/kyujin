@extends('layouts.manage')

@section('title', '掲載企業')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">掲載企業</h1>

        <a href="{{ route('admin.companies.create') }}"
           class="rounded px-4 py-2 text-sm font-semibold text-white"
           style="background-color: var(--theme-color)">
            掲載企業を追加
        </a>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap items-end gap-3">
        <div>
            <label for="keyword" class="block text-xs text-[var(--ink-soft)]">企業名で絞り込む</label>
            <input id="keyword" name="keyword" value="{{ $keyword }}"
                   class="mt-1 rounded border border-[var(--border)] px-3 py-2 text-sm">
        </div>

        <div>
            <label for="status" class="block text-xs text-[var(--ink-soft)]">ステータス</label>
            <select id="status" name="status" class="mt-1 rounded border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-sm">
                <option value="">すべて</option>
                <option value="active" @selected($status === 'active')>掲載中</option>
                <option value="suspended" @selected($status === 'suspended')>停止中</option>
                <option value="archived" @selected($status === 'archived')>契約終了</option>
            </select>
        </div>

        <button type="submit" class="rounded border border-[var(--border)] bg-[var(--surface)] px-4 py-2 text-sm">絞り込む</button>
    </form>

    <div class="mt-6 overflow-x-auto rounded border border-[var(--border)] bg-[var(--surface)]">
        <table class="w-full text-sm">
            <thead class="border-b border-[var(--border)] bg-[var(--bg)] text-left text-xs text-[var(--ink-soft)]">
                <tr>
                    <th class="px-4 py-3">企業名</th>
                    <th class="px-4 py-3">ステータス</th>
                    <th class="px-4 py-3">担当者</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @forelse ($companies as $company)
                    <tr>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.companies.show', $company) }}" class="font-medium hover:underline">
                                {{ $company->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <x-company-status-badge :status="$company->status" />
                        </td>
                        <td class="px-4 py-3 text-[var(--ink-soft)]">{{ $company->users_count }} 名</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.companies.edit', $company) }}" class="text-[var(--ink-soft)] hover:underline">編集</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-[var(--muted)]">
                            掲載企業がまだ登録されていません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $companies->links() }}</div>
@endsection
