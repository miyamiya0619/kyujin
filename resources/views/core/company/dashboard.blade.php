@extends('layouts.manage')

@section('title', 'ダッシュボード')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <h1 class="text-xl font-bold">ダッシュボード</h1>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('company.profile.edit') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">企業情報</p>
            <p class="mt-1 text-sm text-gray-600">求人ページに表示される自社の紹介</p>
        </a>

        <a href="{{ route('company.workplaces.index') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">事業所</p>
            <p class="mt-1 text-sm text-gray-600">特養・デイサービスなどの拠点情報</p>
        </a>

        <a href="{{ route('company.job-postings.index') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="font-semibold">求人</p>
            <p class="mt-1 text-sm text-gray-600">求人の登録・編集・複製</p>
        </a>

        <a href="{{ route('company.applications.index') }}"
           class="rounded border border-gray-200 bg-white p-5 hover:border-gray-400">
            <p class="flex items-center gap-2 font-semibold">
                応募者
                @if ($newApplicationsCount > 0)
                    <span class="rounded-full bg-red-600 px-2 py-0.5 text-xs font-bold text-white">
                        未対応 {{ $newApplicationsCount }}
                    </span>
                @endif
            </p>
            <p class="mt-1 text-sm text-gray-600">応募者一覧・選考ステータス</p>
        </a>
    </div>

    <div class="mt-6 rounded border border-gray-200 bg-white p-5">
        <h2 class="text-sm font-semibold text-gray-700">掲載プラン</h2>

        @if ($currentPlan)
            <p class="mt-2 text-sm">現在のプラン: <span class="font-medium">{{ $currentPlan->name }}</span></p>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-xs text-gray-500">求人の残枠</dt>
                    <dd class="font-medium">{{ $remainingJobPostingSlots === null ? '無制限' : "{$remainingJobPostingSlots} 件" }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">事業所の残枠</dt>
                    <dd class="font-medium">{{ $remainingWorkplaceSlots === null ? '無制限' : "{$remainingWorkplaceSlots} 件" }}</dd>
                </div>
            </dl>
        @else
            <p class="mt-2 text-sm text-gray-600">
                掲載プランが割り当てられていません(制限なくご利用いただけます)。
            </p>
        @endif
    </div>
@endsection
