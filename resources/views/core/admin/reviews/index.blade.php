@extends('layouts.manage')

@section('title', '審査待ち一覧')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <h1 class="text-xl font-bold">審査待ち一覧</h1>
    <p class="mt-1 text-sm text-[var(--ink-soft)]">{{ $jobPostings->count() }} 件の求人が審査待ちです。</p>

    <div class="mt-6 space-y-4">
        @forelse ($jobPostings as $jobPosting)
            <div class="rounded border border-[var(--border)] bg-[var(--surface)] p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-[var(--muted)]">{{ $jobPosting->company->name }} / {{ $jobPosting->workplace->name }}</p>
                        <h2 class="mt-1 text-lg font-semibold">{{ $jobPosting->title }}</h2>
                    </div>
                    <x-job-posting-status-badge :status="$jobPosting->status" />
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-xs text-[var(--muted)]">職種</dt>
                        <dd>{{ $jobPosting->jobCategory?->name ?? '未設定' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-[var(--muted)]">雇用形態</dt>
                        <dd>{{ $jobPosting->employmentType?->name ?? '未設定' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-[var(--muted)]">給与</dt>
                        <dd>
                            @if ($jobPosting->salary_min || $jobPosting->salary_max)
                                {{ number_format($jobPosting->salary_min ?? 0) }}円 〜 {{ number_format($jobPosting->salary_max ?? 0) }}円
                            @else
                                未設定
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-[var(--muted)]">夜勤</dt>
                        <dd>{{ $jobPosting->has_night_shift ? 'あり' : 'なし' }}</dd>
                    </div>
                </dl>

                @if ($jobPosting->description)
                    <p class="mt-4 whitespace-pre-line text-sm text-[var(--ink-soft)]">{{ $jobPosting->description }}</p>
                @endif

                {{-- 法令チェックポイント(SPEC.md 12.3)。人による審査を担保するためのチェックリスト。
                     自動チェックではなく、審査者が目視で確認したことの手がかりとして表示する。 --}}
                <div class="mt-4 rounded border border-yellow-200 bg-yellow-50 p-4 text-sm">
                    <p class="font-semibold text-yellow-900">確認すべきポイント</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-[var(--warning)]">
                        <li>就業場所・業務内容の変更範囲が明示されているか</li>
                        <li>有期契約の場合、更新上限が明示されているか</li>
                        <li>年齢・性別を理由にした募集制限が含まれていないか</li>
                        <li>給与額が最低賃金を下回っていないか</li>
                        <li>健康状態・病歴など要配慮個人情報に触れる記述がないか</li>
                    </ul>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <form method="POST" action="{{ route('admin.job-postings.approve', $jobPosting) }}">
                        @csrf
                        <button type="submit"
                                class="rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                            承認して公開する
                        </button>
                    </form>

                    <button type="button" onclick="document.getElementById('reject-form-{{ $jobPosting->id }}').classList.toggle('hidden')"
                            class="rounded border border-[var(--danger)] px-4 py-2 text-sm font-semibold text-[var(--danger)]">
                        差戻す
                    </button>

                    <a href="{{ route('admin.companies.job-postings.edit', [$jobPosting->company, $jobPosting]) }}"
                       class="text-sm text-[var(--ink-soft)] hover:underline">内容を編集</a>
                </div>

                <form id="reject-form-{{ $jobPosting->id }}"
                      method="POST" action="{{ route('admin.job-postings.reject', $jobPosting) }}"
                      class="mt-4 hidden border-t border-[var(--border)] pt-4">
                    @csrf
                    <x-form.field name="reason" label="差戻し理由" required
                                  help="掲載企業へそのままメールで送られます。具体的に記入してください。">
                        <x-form.textarea name="reason" :rows="3" required />
                    </x-form.field>
                    <button type="submit" class="mt-3 rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        差戻しを確定する
                    </button>
                </form>
            </div>
        @empty
            <p class="rounded border border-[var(--border)] bg-[var(--surface)] p-8 text-center text-[var(--muted)]">
                審査待ちの求人はありません。
            </p>
        @endforelse
    </div>
@endsection
