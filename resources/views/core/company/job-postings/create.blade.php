@extends('layouts.manage')

@section('title', '求人の追加')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <a href="{{ route('company.job-postings.index') }}" class="text-sm text-[var(--ink-soft)] hover:underline">&laquo; 求人一覧</a>

    <h1 class="mt-1 text-xl font-bold">求人の追加</h1>

    <form method="POST" action="{{ route('company.job-postings.store') }}"
          class="mt-6 max-w-2xl rounded border border-[var(--border)] bg-[var(--surface)] p-6">
        @csrf

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
                下書きとして保存する
            </button>
            <a href="{{ route('company.job-postings.index') }}" class="text-sm text-[var(--ink-soft)] hover:underline">キャンセル</a>
        </div>
    </form>
@endsection
