{{--
    求人一覧のカード。トップページ・検索結果・企業ページで共通利用。
    表示に必要なデータは $jobPosting の eager load 済みリレーションから読むだけにする
    (テーマ側でクエリを書かせないため。CLAUDE.md 3.2)。
--}}
@props(['jobPosting'])

<a href="{{ route('public.jobs.show', $jobPosting) }}"
   class="block rounded border border-gray-200 bg-white p-4 hover:border-gray-400 hover:shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            @if ($jobPosting->is_featured)
                <span class="inline-block rounded bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">
                    おすすめ
                </span>
            @endif

            <h3 class="mt-1 truncate font-semibold">{{ $jobPosting->title }}</h3>

            <p class="mt-1 text-sm text-gray-600">
                {{ $jobPosting->company->name }} / {{ $jobPosting->workplace->name }}
            </p>

            <p class="mt-1 text-xs text-gray-500">
                {{ $jobPosting->workplace->locationLabel() }}
                @if ($jobPosting->jobCategory) ・ {{ $jobPosting->jobCategory->name }} @endif
                @if ($jobPosting->employmentType) ・ {{ $jobPosting->employmentType->name }} @endif
            </p>
        </div>

        @if ($jobPosting->salary_min || $jobPosting->salary_max)
            <div class="shrink-0 text-right">
                <p class="text-xs text-gray-500">
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
        <span class="mt-2 inline-block rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700">夜勤あり</span>
    @endif
</a>
