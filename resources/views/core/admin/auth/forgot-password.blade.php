<x-auth-page title="パスワードの再設定" :back-url="route('admin.login')" back-label="ログイン画面へ戻る">
    <x-auth-forgot-form :action="route('admin.password.email')" />
</x-auth-page>
