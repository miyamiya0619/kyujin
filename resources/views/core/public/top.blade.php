@extends('layouts.app')

@section('title', $site->catch_copy ? "{$site->site_name} | {$site->catch_copy}" : $site->site_name)
@section('meta_description', $site->meta_description ?: "介護・医療・福祉の求人情報サイト「{$site->site_name}」。地域・職種・施設形態・資格から{$totalJobPostingCount}件の求人を検索できます。")

@section('content')
    {{-- ============================ ヒーロー ============================ --}}
    <section class="hero-warm">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-14 lg:grid-cols-[1.05fr_.95fr] lg:py-20">
            <div>
                <span class="count-badge">
                    掲載中の求人
                    <span class="count-badge-num">{{ number_format($totalJobPostingCount) }}</span>
                    件
                </span>

                <h1 class="font-display mt-5 text-3xl leading-snug font-semibold sm:text-4xl lg:text-[2.6rem]">
                    {{ $site->catch_copy ?: '介護・医療・福祉のお仕事を探す' }}
                </h1>

                <p class="mt-4 text-[15px] leading-relaxed" style="color: var(--ink-soft)">
                    勤務地・職種・施設形態・保有資格から、あなたに合った職場を探せます。
                    気になる求人は会員登録すると、そのまま応募まで進めます。
                </p>

                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ route('public.jobs.index') }}" class="btn-primary">求人を探す</a>

                    @guest('seeker')
                        @if ($site->enables_member)
                            <a href="{{ route('seeker.register') }}" class="btn-coral">無料で会員登録</a>
                        @endif
                    @else
                        <a href="{{ route('seeker.mypage') }}" class="btn-outline-brand">マイページ</a>
                    @endguest
                </div>

                <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-[13px]" style="color: var(--ink-soft)">
                    <li>✓ 登録・利用は無料</li>
                    <li>✓ 応募はスマホから完結</li>
                    <li>✓ 履歴書は登録内容から自動作成</li>
                </ul>
            </div>

            {{-- キービジュアルが未設定でも空白にならないよう、掲載状況の実数を出す --}}
            <div>
                @if ($site->key_visual_path)
                    <img src="{{ \App\Services\ImageUploadService::url($site->key_visual_path) }}"
                         alt="{{ $site->site_name }}"
                         class="w-full rounded-xl object-cover shadow-sm"
                         style="border: 1px solid var(--accent-line)">
                @else
                    <div class="card p-6" style="border-color: var(--accent-line)">
                        <p class="section-eyebrow">いまの掲載状況</p>

                        <dl class="mt-4 grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-lg py-4" style="background-color: var(--accent-tint)">
                                <dt class="text-xs" style="color: var(--ink-soft)">公開中の求人</dt>
                                <dd class="mt-1 text-2xl font-bold tabular-nums" style="color: var(--accent-strong)">
                                    {{ number_format($totalJobPostingCount) }}
                                </dd>
                            </div>
                            <div class="rounded-lg py-4" style="background-color: var(--brand-tint)">
                                <dt class="text-xs" style="color: var(--ink-soft)">募集エリア</dt>
                                <dd class="mt-1 text-2xl font-bold tabular-nums" style="color: var(--theme-color)">
                                    {{ number_format($prefectureCounts->count()) }}
                                </dd>
                            </div>
                            <div class="rounded-lg py-4" style="background-color: var(--coral-tint)">
                                <dt class="text-xs" style="color: var(--ink-soft)">夜勤あり</dt>
                                <dd class="mt-1 text-2xl font-bold tabular-nums" style="color: var(--coral)">
                                    {{ number_format($nightShiftCount) }}
                                </dd>
                            </div>
                        </dl>

                        <p class="mt-4 text-xs" style="color: var(--muted)">
                            掲載企業から直接届く求人のみを掲載しています。
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ======================= 条件から探す(検索) ======================= --}}
    <section class="mx-auto max-w-6xl px-4 py-10">
        <h2 class="font-display text-xl font-semibold">ご希望の条件から探す</h2>
        <p class="mt-1 text-sm" style="color: var(--ink-soft)">勤務地と職種を選ぶだけで検索できます。</p>

        <div class="mt-4">
            <x-job-search-form :prefectures="$prefectures"
                               :cities="collect()"
                               :job-categories="$jobCategories"
                               :employment-types="$employmentTypes"
                               :compact="true"
                               :emphasis="true" />
        </div>
    </section>

    {{-- ========================== 特集(入口タイル) ========================== --}}
    @if ($nightShiftCount > 0 || $featuredEmploymentTypes->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pb-10">
            <div class="grid gap-3 sm:grid-cols-3">
                @if ($nightShiftCount > 0)
                    <a href="{{ route('public.jobs.index', ['night_shift_only' => 1]) }}" class="tile">
                        <p class="tile-label">夜勤ありの求人</p>
                        <p class="tile-count">{{ number_format($nightShiftCount) }} 件</p>
                    </a>
                @endif

                @foreach ($featuredEmploymentTypes as $employmentType)
                    @continue($employmentTypeCounts->get($employmentType->id, 0) === 0)
                    <a href="{{ route('public.jobs.index', ['employment_type_id' => $employmentType->id]) }}" class="tile">
                        <p class="tile-label">{{ $employmentType->name }}の求人</p>
                        <p class="tile-count">{{ number_format($employmentTypeCounts->get($employmentType->id, 0)) }} 件</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ========================== エリアから探す ========================== --}}
    @if ($prefectureGroups->isNotEmpty())
        <section class="section-cream">
            <div class="mx-auto max-w-6xl px-4 py-12">
                <p class="section-eyebrow">AREA</p>
                <h2 class="font-display mt-1 text-xl font-semibold">エリアから探す</h2>

                <div class="mt-5">
                    @foreach ($prefectureGroups as $region => $prefecturesInRegion)
                        <details class="acc" @if ($loop->first) open @endif>
                            <summary>{{ $region }}</summary>
                            <div class="acc-body">
                                <div class="grid gap-x-6 sm:grid-cols-2 lg:grid-cols-4">
                                    @foreach ($prefecturesInRegion as $prefecture)
                                        <a href="{{ route('public.jobs.index', ['prefecture_id' => $prefecture->id]) }}"
                                           class="browse-link">
                                            <span>{{ $prefecture->name }}</span>
                                            <span class="browse-count">{{ number_format($prefectureCounts->get($prefecture->id, 0)) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ======================= 人気の条件(こだわり) ======================= --}}
    @if ($jobFeatures->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12">
            <p class="section-eyebrow">CONDITION</p>
            <h2 class="font-display mt-1 text-xl font-semibold">こだわり条件から探す</h2>

            <div class="mt-5 flex flex-wrap gap-2">
                @foreach ($jobFeatures as $feature)
                    <a href="{{ route('public.jobs.index', ['feature_ids' => [$feature->id]]) }}" class="chip">
                        {{ $feature->name }}
                        <span class="chip-count">{{ number_format($jobFeatureCounts->get($feature->id, 0)) }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================ 新着求人 ============================ --}}
    <section class="mx-auto max-w-6xl px-4 pb-12">
        <div class="flex items-baseline justify-between">
            <div>
                <p class="section-eyebrow">NEW</p>
                <h2 class="font-display mt-1 text-xl font-semibold">新着求人</h2>
            </div>
            <a href="{{ route('public.jobs.index') }}" class="text-sm hover:underline">
                すべての求人を見る &raquo;
            </a>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($newJobPostings as $jobPosting)
                <x-job-posting-card :job-posting="$jobPosting" />
            @empty
                <p class="card col-span-full p-8 text-center" style="color: var(--muted)">
                    現在募集中の求人はありません。
                </p>
            @endforelse
        </div>
    </section>

    {{-- =========================== おすすめ求人 =========================== --}}
    @if ($featuredJobPostings->isNotEmpty())
        <section class="section-cream">
            <div class="mx-auto max-w-6xl px-4 py-12">
                <p class="section-eyebrow">PICK UP</p>
                <h2 class="font-display mt-1 text-xl font-semibold">おすすめ求人</h2>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredJobPostings as $jobPosting)
                        <x-job-posting-card :job-posting="$jobPosting" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== 職種・施設形態・雇用形態・資格 ==================== --}}
    <section class="mx-auto max-w-6xl px-4 py-12">
        <p class="section-eyebrow">SEARCH</p>
        <h2 class="font-display mt-1 text-xl font-semibold">条件を選んで探す</h2>

        <div class="mt-5 grid gap-3 lg:grid-cols-2">
            @if ($jobCategories->isNotEmpty())
                <details class="acc" open>
                    <summary>職種から探す</summary>
                    <div class="acc-body">
                        <div class="grid gap-x-6 sm:grid-cols-2">
                            @foreach ($jobCategories as $jobCategory)
                                <a href="{{ route('public.jobs.index', ['job_category_id' => $jobCategory->id]) }}"
                                   class="browse-link">
                                    <span>{{ $jobCategory->name }}</span>
                                    <span class="browse-count">{{ number_format($jobCategoryCounts->get($jobCategory->id, 0)) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif

            @if ($facilityTypes->isNotEmpty())
                <details class="acc" open>
                    <summary>施設形態から探す</summary>
                    <div class="acc-body">
                        <div class="grid gap-x-6 sm:grid-cols-2">
                            @foreach ($facilityTypes as $facilityType)
                                <a href="{{ route('public.jobs.index', ['facility_type_id' => $facilityType->id]) }}"
                                   class="browse-link">
                                    <span>{{ $facilityType->displayName() }}</span>
                                    <span class="browse-count">{{ number_format($facilityTypeCounts->get($facilityType->id, 0)) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif

            @if ($employmentTypes->isNotEmpty())
                <details class="acc">
                    <summary>雇用形態から探す</summary>
                    <div class="acc-body">
                        <div class="grid gap-x-6 sm:grid-cols-2">
                            @foreach ($employmentTypes as $employmentType)
                                <a href="{{ route('public.jobs.index', ['employment_type_id' => $employmentType->id]) }}"
                                   class="browse-link">
                                    <span>{{ $employmentType->name }}</span>
                                    <span class="browse-count">{{ number_format($employmentTypeCounts->get($employmentType->id, 0)) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif

            @if ($qualifications->isNotEmpty())
                <details class="acc">
                    <summary>保有資格から探す</summary>
                    <div class="acc-body">
                        <div class="flex flex-wrap gap-2 pt-3">
                            @foreach ($qualifications as $qualification)
                                <a href="{{ route('public.jobs.index', ['qualification_id' => $qualification->id]) }}"
                                   class="chip">{{ $qualification->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif
        </div>
    </section>

    {{-- ========================== ご利用の流れ ========================== --}}
    @if ($site->enables_member)
        <section class="section-cream">
            <div class="mx-auto max-w-6xl px-4 py-12">
                <p class="section-eyebrow">FLOW</p>
                <h2 class="font-display mt-1 text-xl font-semibold">応募までの流れ</h2>

                <ol class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['1', '会員登録', 'メールアドレスとお名前だけで登録できます。費用はかかりません。'],
                        ['2', 'プロフィールを入力', '保有資格と職務経歴を登録しておくと、応募のたびに書き直す必要がありません。'],
                        ['3', '求人を探して応募', '気になる求人から応募すると、登録内容がそのまま履歴書として送られます。'],
                        ['4', '選考・面接', '掲載企業から直接連絡が届きます。選考状況はマイページで確認できます。'],
                    ] as [$number, $stepTitle, $stepBody])
                        <li class="card p-5">
                            <span class="step-num">{{ $number }}</span>
                            <h3 class="mt-3 font-semibold">{{ $stepTitle }}</h3>
                            <p class="mt-2 text-[13px] leading-relaxed" style="color: var(--ink-soft)">{{ $stepBody }}</p>
                        </li>
                    @endforeach
                </ol>

                @guest('seeker')
                    <div class="mt-8 text-center">
                        <a href="{{ route('seeker.register') }}" class="btn-coral">無料で会員登録する</a>
                    </div>
                @endguest
            </div>
        </section>
    @endif

    {{--
        ===================== 以下、器だけ用意したセクション =====================

        対応する機能が未実装のため、コントローラは必ず空を渡す(= 描画されない)。
        機能を実装してコントローラで詰めれば、そのままここに出る。

        ⚠ 動作確認のために架空の体験談・記事で埋めないこと。実在しない実績を
          掲げた状態で公開されると、そのまま景品表示法上の問題になりうる。
    --}}

    @if ($seekerVoices->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12">
            <p class="section-eyebrow">VOICE</p>
            <h2 class="font-display mt-1 text-xl font-semibold">ご利用者様の声</h2>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($seekerVoices as $voice)
                    <article class="card p-5">
                        <h3 class="font-semibold">{{ $voice->title }}</h3>
                        <p class="mt-2 text-[13px] leading-relaxed" style="color: var(--ink-soft)">{{ $voice->body }}</p>
                        <p class="mt-3 text-xs" style="color: var(--muted)">{{ $voice->author_label }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($columnArticles->isNotEmpty())
        <section class="section-cream">
            <div class="mx-auto max-w-6xl px-4 py-12">
                <p class="section-eyebrow">COLUMN</p>
                <h2 class="font-display mt-1 text-xl font-semibold">お役立ちコラム</h2>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($columnArticles as $article)
                        <a href="{{ $article->url }}" class="card block p-5 transition-shadow hover:shadow-md">
                            <h3 class="font-semibold">{{ $article->title }}</h3>
                            <p class="mt-2 text-[13px] leading-relaxed" style="color: var(--ink-soft)">{{ $article->excerpt }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
