<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * ログイン処理の共通実装。運営者・掲載企業・求職者の 3 ガードで使う。
 *
 * ガードごとに同じコードを 3 回書かないための基底クラス。
 * 差分はガード名・ビュー・ログイン後の遷移先だけ。
 */
abstract class BaseLoginController extends Controller
{
    /** 総当たりを防ぐための試行回数と、超過時のロック秒数。 */
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    abstract protected function guard(): string;

    abstract protected function view(): string;

    abstract protected function redirectAfterLogin(): string;

    abstract protected function loginRoute(): string;

    /**
     * ログイン時に追加で満たすべき条件。
     * 無効化されたアカウントを弾くために使う(求職者には is_active が無い)。
     *
     * @return array<string, mixed>
     */
    protected function extraCredentials(): array
    {
        return [];
    }

    public function create(): View
    {
        return view($this->view());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $credentials = [...$validated, ...$this->extraCredentials()];

        if (! Auth::guard($this->guard())->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), self::DECAY_SECONDS);

            // アカウントの有無を推測されないよう、原因を問わず同じ文言を返す
            throw ValidationException::withMessages([
                'email' => 'メールアドレスまたはパスワードが正しくありません。',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        // セッション固定攻撃を防ぐために ID を振り直す。
        // invalidate() ではなく regenerate() を使うこと。
        // invalidate() すると他のガードのログイン状態まで消える。
        $request->session()->regenerate();

        return redirect()->intended($this->redirectAfterLogin());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard($this->guard())->logout();

        // このガードだけログアウトする。
        // 同じブラウザで別のガードにログインしている状態は維持する。
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->to($this->loginRoute());
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => "ログインの試行回数が上限に達しました。{$seconds} 秒後にもう一度お試しください。",
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return $this->guard().'|'.Str::transliterate(Str::lower((string) $request->input('email'))).'|'.$request->ip();
    }
}
