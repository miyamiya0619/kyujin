@extends('layouts.manage')

@php($snapshot = $application->resumeSnapshot?->payload ?? [])

@section('title', ($snapshot['name'] ?? '応募者').' | 応募者詳細')
@section('context', auth('company')->user()->company->name)
@section('user-name', auth('company')->user()->name)
@section('logout-url', route('company.logout'))

@section('content')
    <a href="{{ route('company.applications.index') }}" class="text-sm text-gray-600 hover:underline">&laquo; 応募者一覧</a>

    <div class="mt-2 flex items-center justify-between">
        <h1 class="text-xl font-bold">{{ $snapshot['name'] ?? '(氏名不明)' }} さんの応募</h1>
        <x-application-status-badge :status="$application->status" />
    </div>

    <p class="mt-1 text-sm text-gray-600">
        {{ $application->jobPosting->title }} / {{ $application->jobPosting->workplace->name }}
        ・応募日 {{ $application->applied_at->format('Y/m/d') }}
        ・流入元 {{ $application->referrer_source }}
    </p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded border border-gray-200 bg-white p-6">
                <h2 class="text-sm font-semibold text-gray-700">応募時点の履歴書</h2>
                <p class="mt-1 text-xs text-gray-500">
                    本人が後からプロフィールを変更しても、ここの内容は応募時点のまま変わりません。
                </p>

                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-gray-500">お名前</dt>
                        <dd class="mt-1">{{ $snapshot['name'] ?? '-' }}({{ $snapshot['name_kana'] ?? '-' }})</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">メールアドレス</dt>
                        <dd class="mt-1">{{ $snapshot['email'] ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">電話番号</dt>
                        <dd class="mt-1">{{ $snapshot['tel'] ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">生年月日</dt>
                        <dd class="mt-1">{{ $snapshot['birthday'] ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">居住地</dt>
                        <dd class="mt-1">{{ $snapshot['prefecture'] ?? '' }}{{ $snapshot['city'] ?? '' }}</dd>
                    </div>
                </dl>

                @if (! empty($snapshot['qualifications']))
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs text-gray-500">保有資格</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($snapshot['qualifications'] as $qualification)
                                <span class="rounded bg-gray-100 px-2 py-1 text-xs">{{ $qualification }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (! empty($snapshot['experiences']))
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs text-gray-500">職務経歴</p>
                        <div class="mt-2 space-y-3">
                            @foreach ($snapshot['experiences'] as $experience)
                                <div class="rounded border border-gray-100 p-3 text-sm">
                                    <p class="font-medium">{{ $experience['organization_name'] }}</p>
                                    <p class="text-xs text-gray-600">
                                        {{ $experience['job_title'] }}
                                        ({{ $experience['started_on'] }} 〜 {{ $experience['ended_on'] ?? '在籍中' }})
                                    </p>
                                    @if (! empty($experience['description']))
                                        <p class="mt-1 whitespace-pre-line text-xs text-gray-600">{{ $experience['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($application->message)
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs text-gray-500">応募メッセージ</p>
                        <p class="mt-2 whitespace-pre-line text-sm">{{ $application->message }}</p>
                    </div>
                @endif
            </div>

            <div class="rounded border border-gray-200 bg-white p-6">
                <h2 class="text-sm font-semibold text-gray-700">社内メモ</h2>

                <form method="POST" action="{{ route('company.applications.notes.store', $application) }}" class="mt-4">
                    @csrf
                    <x-form.field name="body" label="メモを追加">
                        <x-form.textarea name="body" :rows="3" />
                    </x-form.field>
                    <button type="submit" class="mt-3 rounded px-4 py-2 text-sm font-semibold text-white"
                            style="background-color: var(--theme-color)">
                        追加する
                    </button>
                </form>

                <div class="mt-4 space-y-3">
                    @forelse ($application->notes as $note)
                        <div class="rounded border border-gray-100 bg-gray-50 p-3 text-sm">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500">
                                    {{ $note->companyUser?->name ?? '(退職済み)' }}・{{ $note->created_at->format('Y/m/d H:i') }}
                                </p>
                                <form method="POST" action="{{ route('company.applications.notes.destroy', [$application, $note]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:underline">削除</button>
                                </form>
                            </div>
                            <p class="mt-1 whitespace-pre-line">{{ $note->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">メモはまだありません。</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded border border-gray-200 bg-white p-6">
                <h2 class="text-sm font-semibold text-gray-700">選考ステータス</h2>

                <form method="POST" action="{{ route('company.applications.status.update', $application) }}" class="mt-3">
                    @csrf
                    @method('PUT')
                    <x-form.select name="status" :value="$application->status" :options="$statusOptions" />
                    <button type="submit" class="mt-3 w-full rounded px-4 py-2 text-sm font-semibold text-white"
                            style="background-color: var(--theme-color)">
                        更新する
                    </button>
                </form>
            </div>

            <div class="rounded border border-gray-200 bg-white p-6">
                <h2 class="text-sm font-semibold text-gray-700">変更履歴</h2>
                <div class="mt-3 space-y-2">
                    @forelse ($application->statusLogs as $log)
                        <p class="text-xs text-gray-600">
                            {{ $log->created_at->format('Y/m/d H:i') }}
                            {{ $statusOptions[$log->from_status] ?? $log->from_status }}
                            →
                            {{ $statusOptions[$log->to_status] ?? $log->to_status }}
                            ({{ $log->companyUser?->name ?? '(退職済み)' }})
                        </p>
                    @empty
                        <p class="text-xs text-gray-500">変更履歴はまだありません。</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
