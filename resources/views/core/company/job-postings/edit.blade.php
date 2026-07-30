@extends('layouts.manage')

@section('title', $jobPosting->title.' の編集')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <a href="{{ route('company.job-postings.index') }}" class="text-sm text-gray-600 hover:underline">&laquo; 求人一覧</a>

    <div class="mt-1 flex items-center gap-3">
        <h1 class="text-xl font-bold">{{ $jobPosting->title }} の編集</h1>
        <x-job-posting-status-badge :status="$jobPosting->status" />
    </div>

    @if ($jobPosting->status === 'published')
        <p class="mt-2 text-xs text-yellow-700">
            公開中の求人を編集すると、審査待ちに戻ります。
        </p>
    @endif

    <form method="POST" action="{{ route('company.job-postings.update', $jobPosting) }}"
          class="mt-6 max-w-2xl rounded border border-gray-200 bg-white p-6">
        @csrf
        @method('PUT')

        <x-job-posting-form-fields
            :job-posting="$jobPosting"
            :workplaces="$workplaces"
            :job-categories="$jobCategories"
            :employment-types="$employmentTypes"
            :qualifications="$qualifications"
            :job-features="$jobFeatures"
            :selected-qualification-ids="$selectedQualificationIds"
            :selected-feature-ids="$selectedFeatureIds" />

        <div class="mt-8 flex items-center gap-3">
            <button type="submit" class="rounded px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--theme-color)">
                更新する
            </button>
            <a href="{{ route('company.job-postings.index') }}" class="text-sm text-gray-600 hover:underline">キャンセル</a>
        </div>
    </form>
@endsection
