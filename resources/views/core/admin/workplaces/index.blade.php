@extends('layouts.manage')

@section('title', $company->name.' の事業所')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.companies.show', $company) }}" class="text-sm text-[var(--ink-soft)] hover:underline">
                &laquo; {{ $company->name }}
            </a>
            <h1 class="mt-1 text-xl font-bold">事業所</h1>
        </div>

        <a href="{{ route('admin.companies.workplaces.create', $company) }}"
           class="rounded px-4 py-2 text-sm font-semibold text-white"
           style="background-color: var(--theme-color)">
            事業所を追加
        </a>
    </div>

    @if (session('error'))
        <div class="mt-4 rounded border border-[var(--danger)] bg-[var(--danger-bg)] px-4 py-3 text-sm text-[var(--danger)]">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-6 overflow-x-auto rounded border border-[var(--border)] bg-[var(--surface)]">
        <table class="w-full text-sm">
            <thead class="border-b border-[var(--border)] bg-[var(--bg)] text-left text-xs text-[var(--ink-soft)]">
                <tr>
                    <th class="px-4 py-3">事業所名</th>
                    <th class="px-4 py-3">施設形態</th>
                    <th class="px-4 py-3">所在地</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @forelse ($workplaces as $workplace)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $workplace->name }}</td>
                        <td class="px-4 py-3 text-[var(--ink-soft)]">{{ $workplace->facilityType?->displayName() }}</td>
                        <td class="px-4 py-3 text-[var(--ink-soft)]">{{ $workplace->locationLabel() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.companies.workplaces.edit', [$company, $workplace]) }}"
                                   class="text-[var(--ink-soft)] hover:underline">編集</a>
                                <form method="POST" action="{{ route('admin.companies.workplaces.destroy', [$company, $workplace]) }}"
                                      onsubmit="return confirm('この事業所を削除しますか?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">削除</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-[var(--muted)]">
                            事業所がまだ登録されていません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
