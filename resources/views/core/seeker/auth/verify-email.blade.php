<x-auth-page title="メールアドレスの確認">
    <p class="text-sm" style="color: var(--ink-soft)">
        ご登録いただいたメールアドレス宛に確認メールを送信しました。
        メール内のリンクを開いて、メールアドレスの確認を完了してください。
    </p>

    <p class="mt-3 text-sm" style="color: var(--ink-soft)">
        メールが届かない場合は、下のボタンから再送できます。
    </p>

    <form method="POST" action="{{ route('seeker.verification.send') }}" class="mt-6">
        @csrf
        <button type="submit" class="btn-primary w-full">確認メールを再送する</button>
    </form>

    <form method="POST" action="{{ route('seeker.logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="w-full text-sm hover:underline" style="color: var(--ink-soft)">
            ログアウト
        </button>
    </form>
</x-auth-page>
