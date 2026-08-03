@extends('layouts.app')

@section('title', "応募 | {$jobPosting->title}")

@section('content')
    <section class="mx-auto max-w-xl px-4 py-8">
        <a href="{{ route('public.jobs.show', $jobPosting) }}" class="text-sm text-[var(--ink-soft)] hover:underline">&laquo; 求人詳細へ戻る</a>

        <h1 class="mt-2 text-xl font-bold">この求人に応募する</h1>
        <p class="mt-1 text-sm text-[var(--ink-soft)]">{{ $jobPosting->title }} / {{ $jobPosting->company->name }}</p>

        @if ($errors->any())
            <div class="mt-4 rounded border border-[var(--danger)] bg-[var(--danger-bg)] px-4 py-3 text-sm text-[var(--danger)]">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($alreadyApplied)
            <div class="mt-6 rounded border border-[var(--border)] bg-[var(--surface)] p-6 text-sm">
                この求人にはすでに応募済みです。選考状況はマイページからご確認いただけます。
                <a href="{{ route('seeker.mypage') }}" class="ml-1 text-sm font-semibold hover:underline"
                   style="color: var(--theme-color)">マイページへ</a>
            </div>
        @else
            <form method="POST" action="{{ route('public.jobs.apply.store', $jobPosting) }}"
                  class="mt-6 rounded border border-[var(--border)] bg-[var(--surface)] p-6">
                @csrf

                <div class="space-y-5">
                    @if ($jobSeeker)
                        <p class="text-sm text-[var(--ink-soft)]">
                            {{ $jobSeeker->name }} さんとして応募します。
                        </p>
                    @else
                        <p class="text-xs text-[var(--muted)]">
                            会員登録がまだの方は、この内容で応募と同時に会員登録も完了します。
                        </p>

                        <x-form.field name="name" label="お名前" required>
                            <x-form.input name="name" required />
                        </x-form.field>

                        <x-form.field name="name_kana" label="お名前(ふりがな)">
                            <x-form.input name="name_kana" />
                        </x-form.field>

                        <x-form.field name="email" label="メールアドレス" required>
                            <x-form.input name="email" type="email" required autocomplete="username" />
                        </x-form.field>

                        <x-form.field name="password" label="パスワード" required>
                            <x-form.input name="password" type="password" required autocomplete="new-password" />
                        </x-form.field>

                        <x-form.field name="password_confirmation" label="パスワード(確認)" required>
                            <x-form.input name="password_confirmation" type="password" required autocomplete="new-password" />
                        </x-form.field>
                    @endif

                    <x-form.field name="message" label="応募メッセージ" help="任意です。">
                        <x-form.textarea name="message" :rows="4" />
                    </x-form.field>
                </div>

                <button type="submit" class="mt-6 w-full rounded px-4 py-2 text-sm font-semibold text-white"
                        style="background-color: var(--theme-color)">
                    応募する
                </button>
            </form>
        @endif
    </section>
@endsection
