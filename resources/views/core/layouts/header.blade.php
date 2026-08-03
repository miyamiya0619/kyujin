{{-- コアの共通ヘッダー。テーマで差し替え可能。 --}}
<header class="border-b" style="border-color: var(--border); background-color: var(--surface)">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            @if ($site->logo_path)
                <img src="{{ \App\Services\ImageUploadService::url($site->logo_path) }}" alt="{{ $site->site_name }}" class="h-8">
            @else
                <span class="font-display text-lg font-bold" style="color: var(--theme-color)">{{ $site->site_name }}</span>
            @endif
        </a>

        <nav class="flex items-center gap-6 text-sm" style="color: var(--ink-soft)">
            <a href="{{ url('/') }}" class="hover:underline">求人を探す</a>
            @auth('seeker')
                <a href="{{ route('seeker.mypage') }}" class="hover:underline">
                    こんにちは、{{ auth('seeker')->user()->name }} さん
                </a>
                <form method="POST" action="{{ route('seeker.logout') }}">
                    @csrf
                    <button type="submit" class="hover:underline">ログアウト</button>
                </form>
            @else
                @if ($site->enables_member)
                    <a href="{{ url('/login') }}" class="hover:underline">ログイン</a>
                @endif
            @endauth
        </nav>
    </div>
</header>
