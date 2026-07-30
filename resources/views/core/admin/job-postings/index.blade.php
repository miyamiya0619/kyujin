@extends('layouts.manage')

@section('title', $company->name.' の求人')
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.companies.show', $company) }}" class="text-sm text-gray-600 hover:underline">
                &laquo; {{ $company->name }}
            </a>
            <h1 class="mt-1 text-xl font-bold">求人</h1>
        </div>

        <a href="{{ route('admin.companies.job-postings.create', $company) }}"
           class="rounded px-4 py-2 text-sm font-semibold text-white"
           style="background-color: var(--theme-color)">
            求人を代行入稿
        </a>
    </div>

    <div class="mt-6 overflow-x-auto rounded border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-left text-xs text-gray-600">
                <tr>
                    <th class="px-4 py-3">求人タイトル</th>
                    <th class="px-4 py-3">事業所</th>
                    <th class="px-4 py-3">ステータス</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($jobPostings as $jobPosting)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $jobPosting->title }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $jobPosting->workplace->name }}</td>
                        <td class="px-4 py-3"><x-job-posting-status-badge :status="$jobPosting->status" /></td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.companies.job-postings.edit', [$company, $jobPosting]) }}"
                                   class="text-gray-600 hover:underline">編集</a>
                                <form method="POST" action="{{ route('admin.companies.job-postings.duplicate', [$company, $jobPosting]) }}">
                                    @csrf
                                    <button type="submit" class="text-gray-600 hover:underline">複製</button>
                                </form>
                                <form method="POST" action="{{ route('admin.companies.job-postings.destroy', [$company, $jobPosting]) }}"
                                      onsubmit="return confirm('この求人を削除しますか?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">削除</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            求人がまだ登録されていません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
