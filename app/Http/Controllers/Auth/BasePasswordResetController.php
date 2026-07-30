<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * パスワード再設定の共通実装。3 ガードで使う。
 *
 * トークンはユーザー種別ごとに別テーブルに保存される(config/auth.php)。
 * 同じメールアドレスを持つ運営者と求職者が互いのトークンを上書きしないようにするため。
 */
abstract class BasePasswordResetController extends Controller
{
    /** config/auth.php の passwords に定義したブローカー名。 */
    abstract protected function broker(): string;

    abstract protected function requestView(): string;

    abstract protected function resetView(): string;

    abstract protected function loginRoute(): string;

    public function request(): View
    {
        return view($this->requestView());
    }

    public function email(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'string', 'email']]);

        Password::broker($this->broker())->sendResetLink($request->only('email'));

        // 送信結果にかかわらず同じ応答を返す。
        // 「そのメールアドレスは登録されていません」と返すと、
        // 誰が登録しているかを外部から調べられてしまう。
        return back()->with('status', 'パスワード再設定のご案内をメールで送信しました。メールが届かない場合は、入力したメールアドレスをご確認ください。');
    }

    public function reset(Request $request, string $token): View
    {
        return view($this->resetView(), [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::broker($this->broker())->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages([
                'email' => 'パスワードを再設定できませんでした。リンクの有効期限が切れている可能性があります。',
            ]);
        }

        return redirect()->to($this->loginRoute())
            ->with('status', 'パスワードを再設定しました。新しいパスワードでログインしてください。');
    }
}
