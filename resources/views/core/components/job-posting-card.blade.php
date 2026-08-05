{{--
    求人一覧のカード。トップページ・検索結果・企業ページで共通利用。
    表示に必要なデータは $jobPosting の eager load 済みリレーションから読むだけにする
    (テーマ側でクエリを書かせないため。CLAUDE.md 3.2)。

    給与・勤務地を行で揃えているのは、求職者が複数の求人を縦に並べて
    見比べるときに視線を横に動かさずに済むようにするため。
--}}
@props(['jobPosting'])

<a href="{{ route('public.jobs.show', $jobPosting) }}"
   class="card flex flex-col p-4 transition-shadow hover:shadow-md">
    @if ($jobPosting->is_featured)
        <span class="tag self-start" style="background-color: var(--accent-tint); color: var(--accent-strong)">
            おすすめ
        </span>
    @endif

    <h3 class="mt-1.5 leading-snug font-semibold" style="color: var(--ink)">{{ $jobPosting->title }}</h3>

    <p class="mt-1 text-[13px]" style="color: var(--ink-soft)">
        {{ $jobPosting->company->name }}
        <span style="color: var(--muted)">/ {{ $jobPosting->workplace->name }}</span>
    </p>

    <dl class="mt-3 space-y-1 border-t pt-3 text-[13px]" style="border-color: var(--border)">
        @if ($jobPosting->salary_min || $jobPosting->salary_max)
            <div class="flex gap-2">
                <dt class="w-12 shrink-0" style="color: var(--muted)">給与</dt>
                <dd class="font-semibold tabular-nums" style="color: var(--accent-strong)">
                    @if ($jobPosting->salary_type === 'monthly') 月給
                    @elseif ($jobPosting->salary_type === 'hourly') 時給
                    @elseif ($jobPosting->salary_type === 'daily') 日給
                    @elseif ($jobPosting->salary_type === 'annual') 年収
                    @endif
                    {{ number_format($jobPosting->salary_min ?? $jobPosting->salary_max) }}円〜
                </dd>
            </div>
        @endif

        <div class="flex gap-2">
            <dt class="w-12 shrink-0" style="color: var(--muted)">勤務地</dt>
            <dd style="color: var(--ink-soft)">{{ $jobPosting->workplace->locationLabel() }}</dd>
        </div>
    </dl>

    <div class="mt-3 flex flex-wrap gap-1.5">
        @if ($jobPosting->jobCategory)
            <span class="tag">{{ $jobPosting->jobCategory->name }}</span>
        @endif
        @if ($jobPosting->employmentType)
            <span class="tag" style="background-color: var(--bg); color: var(--ink-soft)">
                {{ $jobPosting->employmentType->name }}
            </span>
        @endif
        @if ($jobPosting->has_night_shift)
            <span class="tag" style="background-color: var(--coral-tint); color: var(--coral)">夜勤あり</span>
        @endif
    </div>
</a>
