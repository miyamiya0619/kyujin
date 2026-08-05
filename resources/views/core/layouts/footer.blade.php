{{-- コアの共通フッター。テーマで差し替え可能。 --}}
<footer class="border-t" style="border-color: var(--border); background-color: var(--surface)">
    <div class="mx-auto max-w-6xl px-4 py-8 text-sm" style="color: var(--ink-soft)">
        <p class="font-display font-semibold">{{ $site->site_name }}</p>

        <nav class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-[13px]">
            <a href="{{ route('public.jobs.index') }}" class="hover:underline">求人を探す</a>
            @if ($site->enables_member)
                @guest('seeker')
                    <a href="{{ route('seeker.register') }}" class="hover:underline">会員登録</a>
                    <a href="{{ url('/login') }}" class="hover:underline">ログイン</a>
                @else
                    <a href="{{ route('seeker.mypage') }}" class="hover:underline">マイページ</a>
                @endguest
            @endif
            <a href="{{ route('company.login') }}" class="hover:underline">掲載をご検討の企業様へ</a>
        </nav>

        @if ($site->contact_tel || $site->contact_email)
            <p class="mt-2">
                @if ($site->contact_tel)
                    <span>TEL: {{ $site->contact_tel }}</span>
                @endif
                @if ($site->contact_email)
                    <span class="ml-3">{{ $site->contact_email }}</span>
                @endif
            </p>
        @endif

        <p class="mt-4 text-xs" style="color: var(--muted)">&copy; {{ now()->year }} {{ $site->site_name }}</p>
    </div>
</footer>
