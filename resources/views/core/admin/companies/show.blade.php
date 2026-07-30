@extends('layouts.manage')

@section('title', $company->name)
@section('context', '運営管理画面')
@section('user-name', auth('admin')->user()->name)
@section('logout-url', route('admin.logout'))

@section('content')
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold">{{ $company->name }}</h1>
            <x-company-status-badge :status="$company->status" />
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.companies.workplaces.index', $company) }}"
               class="rounded border border-gray-300 bg-white px-4 py-2 text-sm">事業所を管理</a>
            <a href="{{ route('admin.companies.job-postings.index', $company) }}"
               class="rounded border border-gray-300 bg-white px-4 py-2 text-sm">求人を管理</a>
            <a href="{{ route('admin.companies.edit', $company) }}"
               class="rounded border border-gray-300 bg-white px-4 py-2 text-sm">企業情報を編集</a>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- 企業情報 --}}
        <section class="rounded border border-gray-200 bg-white p-6 lg:col-span-1">
            <h2 class="text-sm font-semibold text-gray-700">企業情報</h2>

            @php($currentAssignment = $planAssignments->first(fn ($a) => $a->ends_at === null || $a->ends_at->isFuture()))
            <div class="mt-4 rounded border border-gray-100 bg-gray-50 p-3">
                <p class="text-xs text-gray-500">現在の掲載プラン</p>
                <p class="mt-1 font-medium">{{ $currentAssignment?->postingPlan->name ?? '未割当' }}</p>
            </div>

            @if ($company->logo_path)
                <img src="{{ \App\Services\ImageUploadService::url($company->logo_path) }}"
                     alt="{{ $company->name }}" class="mt-4 h-16 rounded border border-gray-200 bg-white p-1">
            @endif

            <dl class="mt-4 space-y-3 text-sm">
                @if ($company->name_kana)
                    <div><dt class="text-xs text-gray-500">ふりがな</dt><dd>{{ $company->name_kana }}</dd></div>
                @endif
                @if ($company->tel)
                    <div><dt class="text-xs text-gray-500">電話番号</dt><dd>{{ $company->tel }}</dd></div>
                @endif
                @if ($company->prefecture)
                    <div>
                        <dt class="text-xs text-gray-500">所在地</dt>
                        <dd>
                            @if ($company->postal_code)〒{{ $company->postal_code }}<br>@endif
                            {{ $company->prefecture->name }}{{ $company->city?->name }}{{ $company->address }}
                        </dd>
                    </div>
                @endif
                @if ($company->website_url)
                    <div>
                        <dt class="text-xs text-gray-500">ウェブサイト</dt>
                        <dd><a href="{{ $company->website_url }}" target="_blank" rel="noopener"
                               class="break-all hover:underline">{{ $company->website_url }}</a></dd>
                    </div>
                @endif
            </dl>
        </section>

        {{-- 担当者 --}}
        <section class="rounded border border-gray-200 bg-white p-6 lg:col-span-2">
            <h2 class="text-sm font-semibold text-gray-700">担当者アカウント</h2>

            <p class="mt-1 text-xs text-gray-500">
                追加するとパスワード設定の案内メールが送られます。パスワードはこちらでは設定しません。
            </p>

            <table class="mt-4 w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="py-3">
                                <p class="font-medium">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </td>
                            <td class="py-3 text-xs text-gray-600">
                                {{ $user->isOwner() ? '管理者' : '担当者' }}
                            </td>
                            <td class="py-3 text-xs">
                                @if ($user->is_active)
                                    <span class="rounded bg-green-100 px-2 py-1 text-green-800">有効</span>
                                @else
                                    <span class="rounded bg-gray-200 px-2 py-1 text-gray-700">無効</span>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    <form method="POST" action="{{ route('admin.companies.users.resend', [$company, $user]) }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-gray-600 hover:underline">案内を再送</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.companies.users.toggle', [$company, $user]) }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-gray-600 hover:underline">
                                            {{ $user->is_active ? '無効にする' : '有効にする' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-500">
                                担当者がまだ登録されていません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <form method="POST" action="{{ route('admin.companies.users.store', $company) }}"
                  class="mt-6 border-t border-gray-200 pt-6">
                @csrf
                <h3 class="text-sm font-semibold text-gray-700">担当者を追加</h3>

                <div class="mt-3 grid gap-4 sm:grid-cols-3">
                    <x-form.field name="name" label="担当者名" required>
                        <x-form.input name="name" required />
                    </x-form.field>

                    <x-form.field name="email" label="メールアドレス" required>
                        <x-form.input name="email" type="email" required />
                    </x-form.field>

                    <x-form.field name="role" label="権限" required>
                        <x-form.select name="role" value="member"
                                       :options="['owner' => '管理者', 'member' => '担当者']" placeholder="" />
                    </x-form.field>
                </div>

                <button type="submit" class="mt-4 rounded px-4 py-2 text-sm font-semibold text-white"
                        style="background-color: var(--theme-color)">
                    招待メールを送る
                </button>
            </form>
        </section>

        {{-- 掲載プラン --}}
        <section class="rounded border border-gray-200 bg-white p-6 lg:col-span-3">
            <h2 class="text-sm font-semibold text-gray-700">掲載プランの割当履歴</h2>

            <table class="mt-4 w-full text-sm">
                <thead class="text-left text-xs text-gray-500">
                    <tr>
                        <th class="pb-2">プラン</th>
                        <th class="pb-2">適用開始日</th>
                        <th class="pb-2">適用終了日</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($planAssignments as $assignment)
                        <tr>
                            <td class="py-2">{{ $assignment->postingPlan->name }}</td>
                            <td class="py-2">{{ $assignment->starts_at->format('Y-m-d') }}</td>
                            <td class="py-2">{{ $assignment->ends_at?->format('Y-m-d') ?? '(継続中)' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">プランが割り当てられていません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <form method="POST" action="{{ route('admin.companies.plan-assignments.store', $company) }}"
                  class="mt-6 border-t border-gray-200 pt-6">
                @csrf
                <h3 class="text-sm font-semibold text-gray-700">プランを割り当てる</h3>
                <p class="mt-1 text-xs text-gray-500">
                    新しいプランを割り当てると、現在有効なプランは適用終了日が自動設定されます。
                </p>

                <div class="mt-3 grid gap-4 sm:grid-cols-3">
                    <x-form.field name="posting_plan_id" label="掲載プラン" required>
                        <x-form.select name="posting_plan_id" :options="$postingPlans->pluck('name', 'id')" required />
                    </x-form.field>

                    <x-form.field name="starts_at" label="適用開始日" required>
                        <x-form.input name="starts_at" type="date" :value="now()->toDateString()" required />
                    </x-form.field>
                </div>

                <button type="submit" class="mt-4 rounded px-4 py-2 text-sm font-semibold text-white"
                        style="background-color: var(--theme-color)">
                    割り当てる
                </button>
            </form>
        </section>
    </div>
@endsection
