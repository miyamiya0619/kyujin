@extends('layouts.manage')

@section('title', '応募者')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-xl font-bold">
            応募者
            @if ($newCount > 0)
                <span class="rounded-full bg-red-600 px-2 py-0.5 text-xs font-bold text-white">未対応 {{ $newCount }}</span>
            @endif
        </h1>

        <a href="{{ route('company.applications.export', request()->query()) }}"
           class="rounded border border-[var(--border)] px-4 py-2 text-sm font-semibold text-[var(--ink-soft)] hover:bg-[var(--bg)]">
            CSV 出力
        </a>
    </div>

    <form method="GET" action="{{ route('company.applications.index') }}"
          class="mt-6 grid gap-4 rounded border border-[var(--border)] bg-[var(--surface)] p-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-form.field name="status" label="選考ステータス">
            <x-form.select name="status" :value="request('status')" :options="$statusOptions" />
        </x-form.field>

        <x-form.field name="job_posting_id" label="求人">
            <x-form.select name="job_posting_id" :value="request('job_posting_id')"
                           :options="$jobPostings->pluck('title', 'id')" />
        </x-form.field>

        <x-form.field name="workplace_id" label="事業所">
            <x-form.select name="workplace_id" :value="request('workplace_id')"
                           :options="$workplaces->pluck('name', 'id')" />
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

        <x-form.field name="applied_from" label="応募日(from)">
            <x-form.input name="applied_from" type="date" :value="request('applied_from')" />
        </x-form.field>

        <x-form.field name="applied_to" label="応募日(to)">
            <x-form.input name="applied_to" type="date" :value="request('applied_to')" />
        </x-form.field>

        <div class="flex items-end gap-3">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                絞り込む
            </button>
            <a href="{{ route('company.applications.index') }}" class="text-sm text-[var(--ink-soft)] hover:underline">条件をクリア</a>
        </div>
    </form>

    <div class="mt-6 overflow-x-auto rounded border border-[var(--border)] bg-[var(--surface)]">
        <table class="w-full text-sm">
            <thead class="border-b border-[var(--border)] bg-[var(--bg)] text-left text-xs text-[var(--ink-soft)]">
                <tr>
                    <th class="px-4 py-3">求人</th>
                    <th class="px-4 py-3">事業所</th>
                    <th class="px-4 py-3">応募日</th>
                    <th class="px-4 py-3">流入元</th>
                    <th class="px-4 py-3">ステータス</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @forelse ($applications as $application)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $application->jobPosting->title }}</td>
                        <td class="px-4 py-3 text-[var(--ink-soft)]">{{ $application->jobPosting->workplace->name }}</td>
                        <td class="px-4 py-3 text-[var(--ink-soft)]">{{ $application->applied_at->format('Y/m/d') }}</td>
                        <td class="px-4 py-3 text-[var(--ink-soft)]">{{ $application->referrer_source }}</td>
                        <td class="px-4 py-3"><x-application-status-badge :status="$application->status" /></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('company.applications.show', $application) }}"
                               class="font-medium hover:underline" style="color: var(--theme-color)">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-[var(--muted)]">
                            該当する応募者がいません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
