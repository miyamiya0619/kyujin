@extends('layouts.manage')

@section('title', '媒体別のアグリゲーション効果')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">媒体別のアグリゲーション効果</h1>
    <p class="mt-1 text-sm text-[var(--ink-soft)]">
        掲載しただけで応募が来ているかを媒体ごとに確認できます。
    </p>

    <div class="mt-6 overflow-x-auto rounded border border-[var(--border)] bg-[var(--surface)]">
        <table class="w-full text-sm">
            <thead class="border-b border-[var(--border)] bg-[var(--bg)] text-left text-xs text-[var(--ink-soft)]">
                <tr>
                    <th class="px-4 py-3">流入元</th>
                    <th class="px-4 py-3">配信中の求人件数</th>
                    <th class="px-4 py-3">フィード最終生成日時</th>
                    <th class="px-4 py-3">応募件数(累計)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @foreach ($mediaNames as $source => $label)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $label }}</td>
                        <td class="px-4 py-3 text-[var(--ink-soft)]">
                            @if (isset($feeds[$source]))
                                {{ $feeds[$source]['job_count'] }} 件
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-[var(--ink-soft)]">
                            @if (isset($feeds[$source]))
                                {{ \Illuminate\Support\Carbon::parse($feeds[$source]['generated_at'])->format('Y/m/d H:i') }}
                            @else
                                未生成
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold">{{ $applicationCounts[$source] ?? 0 }} 件</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="mt-4 text-xs text-[var(--muted)]">
        「配信中の求人件数」「フィード最終生成日時」は Indeed・求人ボックス・スタンバイの
        XML フィードのみ対象です(Google しごと検索と自社サイトはページ単位のため対象外)。
        フィードは日次のバッチ処理(<code>feeds:generate</code>)で生成されます。
    </p>
@endsection
