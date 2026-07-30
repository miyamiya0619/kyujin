{{--
    ログインフォームの中身。3 つのガードで共通。
    差分は送信先(action)とパスワード再設定へのリンク(forgotUrl)だけ。
--}}
@props(['action', 'forgotUrl'])

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}"
               required autofocus autocomplete="username"
               class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">パスワード</label>
        <input id="password" name="password" type="password"
               required autocomplete="current-password"
               class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none">
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-gray-300">
            ログイン状態を保持する
        </label>

        <a href="{{ $forgotUrl }}" class="text-sm text-gray-600 hover:underline">パスワードを忘れた方</a>
    </div>

    <button type="submit"
            class="w-full rounded px-4 py-2 text-sm font-semibold text-white"
            style="background-color: var(--theme-color)">
        ログイン
    </button>
</form>
