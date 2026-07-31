@extends('layouts.app')

@section('title', "{$jobPosting->title} | {$jobPosting->company->name}")
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($jobPosting->description ?? $jobPosting->title), 120))
@section('og_title', $jobPosting->title)

@push('head')
    {{-- Google しごと検索向けの構造化データ(SPEC.md 5.1)。--}}
    <script type="application/ld+json">{!! json_encode(app(\App\Services\JobPostingJsonLd::class)->build($jobPosting), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-8">
        <nav class="text-xs text-gray-500">
            <a href="{{ route('public.jobs.index') }}" class="hover:underline">求人を探す</a>
            <span class="mx-1">/</span>
            <span>{{ $jobPosting->title }}</span>
        </nav>

        <div class="mt-4 rounded border border-gray-200 bg-white p-6">
            @if ($jobPosting->is_featured)
                <span class="inline-block rounded bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">
                    おすすめ
                </span>
            @endif

            <h1 class="mt-2 text-2xl font-bold">{{ $jobPosting->title }}</h1>

            <p class="mt-2 text-sm text-gray-600">
                <a href="{{ route('public.companies.show', $jobPosting->company) }}" class="hover:underline">
                    {{ $jobPosting->company->name }}
                </a>
                / {{ $jobPosting->workplace->name }}
            </p>

            <div class="mt-6">
                <a href="{{ route('seeker.login') }}"
                   class="inline-block rounded px-6 py-3 text-sm font-semibold text-white"
                   style="background-color: var(--theme-color)">
                    この求人に応募する
                </a>
                <p class="mt-1 text-xs text-gray-500">応募機能は準備中です(T-12 で実装)。</p>
            </div>

            <dl class="mt-6 grid grid-cols-1 gap-4 border-t border-gray-100 pt-6 sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-gray-500">給与</dt>
                    <dd class="mt-1">
                        @if ($jobPosting->salary_min || $jobPosting->salary_max)
                            @if ($jobPosting->salary_type === 'monthly') 月給
                            @elseif ($jobPosting->salary_type === 'hourly') 時給
                            @elseif ($jobPosting->salary_type === 'daily') 日給
                            @elseif ($jobPosting->salary_type === 'annual') 年収
                            @endif
                            {{ number_format($jobPosting->salary_min ?? 0) }}円
                            @if ($jobPosting->salary_max) 〜 {{ number_format($jobPosting->salary_max) }}円 @endif
                        @else
                            応相談
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">雇用形態</dt>
                    <dd class="mt-1">{{ $jobPosting->employmentType?->name ?? '未設定' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">職種</dt>
                    <dd class="mt-1">{{ $jobPosting->jobCategory?->name ?? '未設定' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500">夜勤</dt>
                    <dd class="mt-1">{{ $jobPosting->has_night_shift ? 'あり' : 'なし' }}</dd>
                </div>

                @if ($jobPosting->working_hours)
                    <div>
                        <dt class="text-xs text-gray-500">勤務時間</dt>
                        <dd class="mt-1 whitespace-pre-line">{{ $jobPosting->working_hours }}</dd>
                    </div>
                @endif

                @if ($jobPosting->holidays)
                    <div>
                        <dt class="text-xs text-gray-500">休日・休暇</dt>
                        <dd class="mt-1 whitespace-pre-line">{{ $jobPosting->holidays }}</dd>
                    </div>
                @endif
            </dl>

            @if ($jobPosting->qualifications->isNotEmpty())
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <p class="text-xs text-gray-500">必要資格</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($jobPosting->qualifications as $qualification)
                            <span class="rounded bg-gray-100 px-2 py-1 text-xs">{{ $qualification->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($jobPosting->features->isNotEmpty())
                <div class="mt-4">
                    <p class="text-xs text-gray-500">こだわり条件</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($jobPosting->features as $feature)
                            <span class="rounded bg-gray-100 px-2 py-1 text-xs">{{ $feature->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($jobPosting->description)
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <p class="text-xs text-gray-500">仕事内容</p>
                    <p class="mt-2 whitespace-pre-line text-sm">{{ $jobPosting->description }}</p>
                </div>
            @endif

            @if ($jobPosting->benefits)
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <p class="text-xs text-gray-500">待遇・福利厚生</p>
                    <p class="mt-2 whitespace-pre-line text-sm">{{ $jobPosting->benefits }}</p>
                </div>
            @endif

            <div class="mt-6 border-t border-gray-100 pt-6">
                <p class="text-xs text-gray-500">勤務地</p>
                <p class="mt-2 text-sm">
                    {{ $jobPosting->workplace->locationLabel() }}{{ $jobPosting->workplace->address }}
                    @if ($jobPosting->workplace->access)
                        <br>{{ $jobPosting->workplace->access }}
                    @endif
                </p>
                @if ($jobPosting->workplace->photo_path)
                    <img src="{{ $jobPosting->workplace->photoUrl() }}" alt="{{ $jobPosting->workplace->name }}"
                         loading="lazy" class="mt-3 max-h-64 rounded border border-gray-200 object-cover">
                @endif
            </div>
        </div>

        @if ($relatedJobPostings->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-sm font-semibold text-gray-700">{{ $jobPosting->company->name }} の他の求人</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($relatedJobPostings as $related)
                        <x-job-posting-card :job-posting="$related" />
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
