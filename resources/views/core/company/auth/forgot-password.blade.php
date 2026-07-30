<x-auth-page title="パスワードの再設定" :back-url="route('company.login')" back-label="ログイン画面へ戻る">
    <x-auth-forgot-form :action="route('company.password.email')" />
</x-auth-page>
