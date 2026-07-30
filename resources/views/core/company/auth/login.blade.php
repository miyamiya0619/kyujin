<x-auth-page title="掲載企業さま向け管理画面">
    <x-auth-login-form
        :action="route('company.login.store')"
        :forgot-url="route('company.password.request')" />
</x-auth-page>
