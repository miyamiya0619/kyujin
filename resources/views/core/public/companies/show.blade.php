@extends('layouts.app')

@section('title', $company->name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($company->description ?? $company->name), 120))

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-8">
        <div class="rounded border border-gray-200 bg-white p-6">
            <div class="flex items-center gap-4">
                @if ($company->logo_path)
                    <img src="{{ \App\Services\ImageUploadService::url($company->logo_path) }}"
                         alt="{{ $company->name }}" class="h-16 rounded border border-gray-200 bg-white p-1">
                @endif
                <h1 class="text-xl font-bold">{{ $company->name }}</h1>
            </div>

            @if ($company->description)
                <p class="mt-4 whitespace-pre-line text-sm text-gray-700">{{ $company->description }}</p>
            @endif

            <dl class="mt-4 space-y-2 text-sm">
                @if ($company->prefecture)
                    <div>
                        <dt class="text-xs text-gray-500">所在地</dt>
                        <dd>{{ $company->prefecture->name }}{{ $company->city?->name }}{{ $company->address }}</dd>
                    </div>
                @endif
                @if ($company->website_url)
                    <div>
                        <dt class="text-xs text-gray-500">ウェブサイト</dt>
                        <dd><a href="{{ $company->website_url }}" target="_blank" rel="noopener nofollow"
                               class="break-all hover:underline">{{ $company->website_url }}</a></dd>
                    </div>
                @endif
            </dl>
        </div>

        @if ($workplaces->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-sm font-semibold text-gray-700">事業所</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($workplaces as $workplace)
                        <div class="rounded border border-gray-200 bg-white p-4">
                            <p class="font-medium">{{ $workplace->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $workplace->facilityType?->displayName() }}
                                @if ($workplace->prefecture) ・ {{ $workplace->locationLabel() }} @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-8">
            <h2 class="text-sm font-semibold text-gray-700">募集中の求人({{ $jobPostings->count() }} 件)</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @forelse ($jobPostings as $jobPosting)
                    <x-job-posting-card :job-posting="$jobPosting" />
                @empty
                    <p class="col-span-2 rounded border border-gray-200 bg-white p-8 text-center text-gray-500">
                        現在募集中の求人はありません。
                    </p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
