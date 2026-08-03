@extends('layouts.manage')

@section('title', '監査ログ')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">監査ログ</h1>
    <p class="mt-1 text-sm text-[var(--ink-soft)]">
        審査の承認/差戻し・応募者情報の閲覧/CSV出力・掲載プランの変更・サイト設定の変更などの操作履歴です。
    </p>

    <form method="GET" action="{{ route('admin.audit-logs.index') }}"
          class="mt-6 grid gap-4 rounded border border-[var(--border)] bg-[var(--surface)] p-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-form.field name="actor_type" label="操作者の種別">
            <x-form.select name="actor_type" :value="request('actor_type')" :options="[
                'admin' => '運営者',
                'company' => '掲載企業',
                'seeker' => '求職者',
            ]" />
        </x-form.field>

        <x-form.field name="action" label="操作">
            <x-form.select name="action" :value="request('action')" :options="$actions->combine($actions)" />
        </x-form.field>

        <x-form.field name="from" label="期間(from)">
            <x-form.input name="from" type="date" :value="request('from')" />
        </x-form.field>

        <x-form.field name="to" label="期間(to)">
            <x-form.input name="to" type="date" :value="request('to')" />
        </x-form.field>

        <div class="flex items-end gap-3">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                絞り込む
            </button>
            <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-[var(--ink-soft)] hover:underline">条件をクリア</a>
        </div>
    </form>

    <div class="mt-6 overflow-x-auto rounded border border-[var(--border)] bg-[var(--surface)]">
        <table class="w-full text-sm">
            <thead class="border-b border-[var(--border)] bg-[var(--bg)] text-left text-xs text-[var(--ink-soft)]">
                <tr>
                    <th class="px-4 py-3">日時</th>
                    <th class="px-4 py-3">操作者</th>
                    <th class="px-4 py-3">操作</th>
                    <th class="px-4 py-3">対象</th>
                    <th class="px-4 py-3">IP アドレス</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-[var(--ink-soft)]">{{ $log->created_at->format('Y/m/d H:i:s') }}</td>
                        <td class="px-4 py-3">{{ $log->actorLabel() }}({{ $log->actor_type }})</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $log->action }}</td>
                        <td class="px-4 py-3 text-[var(--ink-soft)]">{{ $log->targetLabel() ?? '-' }}</td>
                        <td class="px-4 py-3 text-[var(--muted)]">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-[var(--muted)]">該当するログがありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endsection
