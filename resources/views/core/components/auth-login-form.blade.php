{{--
    ログインフォームの中身。3 つのガードで共通。
    差分は送信先(action)とパスワード再設定へのリンク(forgotUrl)だけ。
--}}
@props(['action', 'forgotUrl'])

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-[var(--ink-soft)]">メールアドレス</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}"
               required autofocus autocomplete="username"
               class="mt-1 w-full rounded border border-[var(--border)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--theme-color,var(--theme-color-fallback))]">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-[var(--ink-soft)]">パスワード</label>
        <input id="password" name="password" type="password"
               required autocomplete="current-password"
               class="mt-1 w-full rounded border border-[var(--border)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--theme-color,var(--theme-color-fallback))]">
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-[var(--ink-soft)]">
            <input type="checkbox" name="remember" value="1" class="rounded border-[var(--border)]">
            ログイン状態を保持する
        </label>

        <a href="{{ $forgotUrl }}" class="text-sm text-[var(--ink-soft)] hover:underline">パスワードを忘れた方</a>
    </div>

    <button type="submit"
            class="w-full rounded px-4 py-2 text-sm font-semibold text-white"
            style="background-color: var(--theme-color)">
        ログイン
    </button>
</form>
