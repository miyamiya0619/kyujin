<x-auth-page title="会員ログイン" :back-url="route('public.top')" back-label="サイトトップへ戻る">
    <x-auth-login-form
        :action="route('seeker.login.store')"
        :forgot-url="route('seeker.password.request')" />

    @if ($site->enables_member)
        <p class="mt-4 text-center text-sm">
            <a href="{{ route('seeker.register') }}" class="hover:underline" style="color: var(--theme-color)">
                会員登録がまだの方はこちら
            </a>
        </p>
    @endif
</x-auth-page>
