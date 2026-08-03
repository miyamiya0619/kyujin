{{--
    求人一覧のカード。トップページ・検索結果・企業ページで共通利用。
    表示に必要なデータは $jobPosting の eager load 済みリレーションから読むだけにする
    (テーマ側でクエリを書かせないため。CLAUDE.md 3.2)。
--}}
@props(['jobPosting'])

<a href="{{ route('public.jobs.show', $jobPosting) }}"
   class="card block p-4 transition-shadow hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            @if ($jobPosting->is_featured)
                <span class="tag" style="background-color: var(--warning-bg); color: var(--warning)">
                    おすすめ
                </span>
            @endif

            <h3 class="mt-1 truncate font-semibold">{{ $jobPosting->title }}</h3>

            <p class="mt-1 text-sm" style="color: var(--ink-soft)">
                {{ $jobPosting->company->name }} / {{ $jobPosting->workplace->name }}
            </p>

            <p class="mt-1 text-xs" style="color: var(--muted)">
                {{ $jobPosting->workplace->locationLabel() }}
                @if ($jobPosting->jobCategory) ・ {{ $jobPosting->jobCategory->name }} @endif
                @if ($jobPosting->employmentType) ・ {{ $jobPosting->employmentType->name }} @endif
            </p>
        </div>

        @if ($jobPosting->salary_min || $jobPosting->salary_max)
            <div class="shrink-0 text-right">
                <p class="text-xs" style="color: var(--muted)">
                    @if ($jobPosting->salary_type === 'monthly') 月給
                    @elseif ($jobPosting->salary_type === 'hourly') 時給
                    @elseif ($jobPosting->salary_type === 'daily') 日給
                    @elseif ($jobPosting->salary_type === 'annual') 年収
                    @endif
                </p>
                <p class="font-semibold" style="color: var(--theme-color)">
                    {{ number_format($jobPosting->salary_min ?? $jobPosting->salary_max) }}円〜
                </p>
            </div>
        @endif
    </div>

    @if ($jobPosting->has_night_shift)
        <span class="tag mt-2" style="background-color: var(--bg); color: var(--ink-soft)">夜勤あり</span>
    @endif
</a>
