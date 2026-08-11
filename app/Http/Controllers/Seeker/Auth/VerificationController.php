<?php

namespace App\Http\Controllers\Seeker\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * 求職者のメールアドレス認証(TASKS.md T-27)。
 *
 * 会員登録直後は自動ログインするが、認証が済むまでは `verified` ミドルウェアで
 * マイページ等への遷移をここへ止める(応募自体はこのミドルウェアの対象外)。
 */
class VerificationController extends Controller
{
    public function notice(): View|RedirectResponse
    {
        if (auth('seeker')->user()->hasVerifiedEmail()) {
            return redirect()->route('seeker.mypage');
        }

        return view('seeker.auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('seeker.mypage')->with('status', 'メールアドレスを確認しました。');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user('seeker')->hasVerifiedEmail()) {
            return redirect()->route('seeker.mypage');
        }

        $request->user('seeker')->sendEmailVerificationNotification();

        return back()->with('status', '確認メールを再送しました。');
    }
}
