<x-auth-page title="会員登録" :back-url="route('public.top')" back-label="サイトトップへ戻る">
    <form method="POST" action="{{ route('seeker.register.store') }}" class="space-y-4">
        @csrf

        <x-form.field name="name" label="お名前" required>
            <x-form.input name="name" required />
        </x-form.field>

        <x-form.field name="name_kana" label="お名前(ふりがな)">
            <x-form.input name="name_kana" />
        </x-form.field>

        <x-form.field name="email" label="メールアドレス" required>
            <x-form.input name="email" type="email" required autocomplete="username" />
        </x-form.field>

        <x-form.field name="password" label="パスワード" required>
            <x-form.input name="password" type="password" required autocomplete="new-password" />
        </x-form.field>

        <x-form.field name="password_confirmation" label="パスワード(確認)" required>
            <x-form.input name="password_confirmation" type="password" required autocomplete="new-password" />
        </x-form.field>

        <button type="submit"
                class="w-full rounded px-4 py-2 text-sm font-semibold text-white"
                style="background-color: var(--theme-color)">
            会員登録する
        </button>
    </form>

    <p class="mt-4 text-center text-sm">
        <a href="{{ route('seeker.login') }}" class="text-[var(--ink-soft)] hover:underline">すでに会員の方はこちら</a>
    </p>
</x-auth-page>
