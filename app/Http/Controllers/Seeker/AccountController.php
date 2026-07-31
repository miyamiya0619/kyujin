<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 求職者の退会。
 *
 * 応募時の履歴書スナップショット(T-12 以降)は選考記録として残る想定
 * (SPEC.md 8.2 の application_resume_snapshots)。本体の求職者レコードのみ削除する。
 */
class AccountController extends Controller
{
    public function destroy(Request $request): RedirectResponse
    {
        $jobSeeker = auth('seeker')->user();

        Auth::guard('seeker')->logout();

        $jobSeeker->delete();

        $request->session()->regenerate();

        return redirect()->route('public.top')->with('status', '退会手続きが完了しました。ご利用ありがとうございました。');
    }
}
