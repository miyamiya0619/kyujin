<x-auth-page title="パスワードの再設定" :back-url="route('seeker.login')" back-label="ログイン画面へ戻る">
    <x-auth-forgot-form :action="route('seeker.password.email')" />
</x-auth-page>
