<?php

namespace App\Http\Controllers\Seeker\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seeker\RegisterRequest;
use App\Models\JobSeeker;
use App\Models\Prefecture;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * 求職者の会員登録。
 * サイト設定で会員機能が OFF のときはルートに `member.enabled` ミドルウェアがかかり、
 * ここには到達しない(EnsureMemberFeatureEnabled を参照)。
 */
class RegisterController extends Controller
{
    public function create(): View
    {
        return view('seeker.auth.register', [
            'prefectures' => Prefecture::selectable()->get(),
            'cities' => collect(),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $jobSeeker = JobSeeker::create([
            ...$request->safe()->except('password'),
            'password' => Hash::make($request->string('password')->toString()),
        ]);

        Auth::guard('seeker')->login($jobSeeker);
        $request->session()->regenerate();

        $jobSeeker->sendEmailVerificationNotification();

        return redirect()->route('seeker.mypage')
            ->with('status', '会員登録が完了しました。確認メールを送信しましたので、メールアドレスの確認をお願いします。');
    }
}
