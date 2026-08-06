{{-- コアの共通ヘッダー。テーマで差し替え可能。 --}}
<header class="border-b" style="border-color: var(--border); background-color: var(--surface)">
    {{--
        メディア名は顧客が自由に設定するため長さが読めない(最大100文字)。
        ロゴ側を min-w-0 + truncate、ナビ側を shrink-0 にして、
        長い名前でもナビが押し出されないようにする。
    --}}
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3.5 sm:gap-4">
        <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-2">
            @if ($site->logo_path)
                <img src="{{ \App\Services\ImageUploadService::url($site->logo_path) }}" alt="{{ $site->site_name }}" class="h-8">
            @else
                <span class="font-display truncate text-base font-bold sm:text-lg" style="color: var(--theme-color)">{{ $site->site_name }}</span>
            @endif
        </a>

        <nav class="flex shrink-0 items-center gap-3 text-sm sm:gap-6" style="color: var(--ink-soft)">
            <a href="{{ route('public.jobs.index') }}" class="hover:underline">求人を探す</a>

            @auth('seeker')
                <a href="{{ route('seeker.mypage') }}" class="hidden hover:underline sm:inline">
                    こんにちは、{{ auth('seeker')->user()->name }} さん
                </a>
                <a href="{{ route('seeker.mypage') }}" class="hover:underline sm:hidden">マイページ</a>
                <form method="POST" action="{{ route('seeker.logout') }}">
                    @csrf
                    <button type="submit" class="hover:underline">ログアウト</button>
                </form>
            @else
                @if ($site->enables_member)
                    <a href="{{ url('/login') }}" class="hover:underline">ログイン</a>
                    <a href="{{ route('seeker.register') }}" class="btn-coral btn-sm">会員登録</a>
                @endif
            @endauth
        </nav>
    </div>
</header>
