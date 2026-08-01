<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 求職者の退会。
 *
 * 応募時の履歴書スナップショットは選考記録として残る(SPEC.md 8.2 の
 * application_resume_snapshots)。`applications.job_seeker_id` は
 * `nullOnDelete` のため、本体の求職者レコードを削除しても応募・スナップショットは
 * 削除されない(CLAUDE.md 3.6。T-17 で `cascadeOnDelete` の設計ミスを修正した)。
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
