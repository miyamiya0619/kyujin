<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * 求職者のマイページ。応募履歴と選考ステータスを表示する(SPEC.md 5.4)。
 */
class MyPageController extends Controller
{
    public function __invoke(): View
    {
        $jobSeeker = auth('seeker')->user();

        return view('seeker.mypage', [
            'applications' => $jobSeeker->applications()
                ->with(['jobPosting', 'jobPosting.company'])
                ->latest('applied_at')
                ->get(),
        ]);
    }
}
