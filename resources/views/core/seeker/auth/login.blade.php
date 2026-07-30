<x-auth-page title="会員ログイン" :back-url="route('public.top')" back-label="サイトトップへ戻る">
    <x-auth-login-form
        :action="route('seeker.login.store')"
        :forgot-url="route('seeker.password.request')" />
</x-auth-page>
