@extends('layouts.manage')

@section('title', '求人の代行入稿')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <a href="{{ route('admin.companies.job-postings.index', $company) }}" class="text-sm text-gray-600 hover:underline">
        &laquo; {{ $company->name }} の求人一覧
    </a>

    <h1 class="mt-1 text-xl font-bold">求人の代行入稿</h1>

    <form method="POST" action="{{ route('admin.companies.job-postings.store', $company) }}"
          class="mt-6 max-w-2xl rounded border border-gray-200 bg-white p-6">
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
            <a href="{{ route('admin.companies.job-postings.index', $company) }}" class="text-sm text-gray-600 hover:underline">
                キャンセル
            </a>
        </div>
    </form>
@endsection
