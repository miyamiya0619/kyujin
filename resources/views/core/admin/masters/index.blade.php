@extends('layouts.manage')

@section('title', $label)
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <a href="{{ route('admin.masters.home') }}" class="text-sm text-[var(--ink-soft)] hover:underline">&laquo; マスタ管理</a>

    <h1 class="mt-1 text-xl font-bold">{{ $label }}</h1>
    <p class="mt-1 text-sm text-[var(--ink-soft)]">
        「↑」「↓」で並び順を変更できます。無効にした項目は入力画面・検索画面の選択肢に出なくなります。
    </p>

    <div class="mt-6 overflow-x-auto rounded border border-[var(--border)] bg-[var(--surface)]">
        <table class="w-full text-sm">
            <thead class="border-b border-[var(--border)] bg-[var(--bg)] text-left text-xs text-[var(--ink-soft)]">
                <tr>
                    <th class="px-4 py-3">名称</th>
                    @if ($hasCategory)
                        <th class="px-4 py-3">カテゴリ</th>
                    @endif
                    <th class="px-4 py-3">コード</th>
                    <th class="px-4 py-3">状態</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody id="master-list" class="divide-y divide-[var(--border)]">
                @forelse ($rows as $row)
                    <tr data-master-id="{{ $row->id }}">
                        <td class="px-4 py-3 font-medium">{{ $row->name }}</td>
                        @if ($hasCategory)
                            <td class="px-4 py-3 text-[var(--ink-soft)]">{{ $row->category }}</td>
                        @endif
                        <td class="px-4 py-3 font-mono text-xs text-[var(--muted)]">{{ $row->code }}</td>
                        <td class="px-4 py-3">
                            @if ($row->is_enabled)
                                <span class="rounded bg-[var(--success-bg)] px-2 py-1 text-xs font-medium text-[var(--success)]">有効</span>
                            @else
                                <span class="rounded bg-[var(--bg)] px-2 py-1 text-xs font-medium text-[var(--ink-soft)]">無効</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button type="button" class="js-move-up text-xs text-[var(--muted)] disabled:opacity-30" @disabled($loop->first)>↑</button>
                                <button type="button" class="js-move-down text-xs text-[var(--muted)] disabled:opacity-30" @disabled($loop->last)>↓</button>
                                <form method="POST" action="{{ route('admin.masters.toggle', [$type, $row]) }}">
                                    @csrf
                                    <button type="submit" class="font-medium hover:underline" style="color: var(--theme-color)">
                                        {{ $row->is_enabled ? '無効にする' : '有効にする' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-[var(--muted)]">項目がありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @once
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[id="master-list"]').forEach((list) => {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    function submitOrder() {
                        const ids = Array.from(list.querySelectorAll('[data-master-id]'))
                            .map(el => el.dataset.masterId);

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = list.dataset.reorderUrl;

                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = csrfToken;
                        form.appendChild(csrf);

                        ids.forEach(id => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids[]';
                            input.value = id;
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                    }

                    list.addEventListener('click', (event) => {
                        const row = event.target.closest('[data-master-id]');
                        if (!row) return;

                        if (event.target.matches('.js-move-up')) {
                            const prev = row.previousElementSibling;
                            if (prev) {
                                list.insertBefore(row, prev);
                                submitOrder();
                            }
                        } else if (event.target.matches('.js-move-down')) {
                            const next = row.nextElementSibling;
                            if (next) {
                                list.insertBefore(next, row);
                                submitOrder();
                            }
                        }
                    });
                });
            });
        </script>
    @endonce

    <script>
        document.getElementById('master-list').dataset.reorderUrl = "{{ route('admin.masters.reorder', $type) }}";
    </script>
@endsection
