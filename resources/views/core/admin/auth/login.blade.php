<x-auth-page title="運営管理画面">
    <x-auth-login-form
        :action="route('admin.login.store')"
        :forgot-url="route('admin.password.request')" />
</x-auth-page>
