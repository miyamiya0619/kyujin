{{-- 新しいパスワードの入力フォーム。3 つのガードで共通。 --}}
@props(['action', 'token', 'email'])

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div>
        <label for="email" class="block text-sm font-medium text-[var(--ink-soft)]">メールアドレス</label>
        <input id="email" name="email" type="email" value="{{ old('email', $email) }}"
               required autocomplete="username"
               class="mt-1 w-full rounded border border-[var(--border)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--theme-color,var(--theme-color-fallback))]">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-[var(--ink-soft)]">新しいパスワード</label>
        <input id="password" name="password" type="password"
               required autocomplete="new-password"
               class="mt-1 w-full rounded border border-[var(--border)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--theme-color,var(--theme-color-fallback))]">
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-[var(--ink-soft)]">新しいパスワード(確認)</label>
        <input id="password_confirmation" name="password_confirmation" type="password"
               required autocomplete="new-password"
               class="mt-1 w-full rounded border border-[var(--border)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--theme-color,var(--theme-color-fallback))]">
    </div>

    <button type="submit"
            class="w-full rounded px-4 py-2 text-sm font-semibold text-white"
            style="background-color: var(--theme-color)">
        パスワードを再設定する
    </button>
</form>
