{{-- パスワード再設定の申請フォーム。3 つのガードで共通。 --}}
@props(['action'])

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf

    <p class="text-sm text-[var(--ink-soft)]">
        登録されているメールアドレスを入力してください。パスワード再設定用のリンクをお送りします。
    </p>

    <div>
        <label for="email" class="block text-sm font-medium text-[var(--ink-soft)]">メールアドレス</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}"
               required autofocus autocomplete="username"
               class="mt-1 w-full rounded border border-[var(--border)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--theme-color,var(--theme-color-fallback))]">
    </div>

    <button type="submit"
            class="w-full rounded px-4 py-2 text-sm font-semibold text-white"
            style="background-color: var(--theme-color)">
        再設定用のリンクを送る
    </button>
</form>
