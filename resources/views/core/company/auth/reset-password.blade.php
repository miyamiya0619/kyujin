<x-auth-page title="新しいパスワードの設定" :back-url="route('company.login')" back-label="ログイン画面へ戻る">
    <x-auth-reset-form
        :action="route('company.password.update')"
        :token="$token"
        :email="$email" />
</x-auth-page>
