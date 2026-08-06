<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Contracts\View\View;

/**
 * 求職者のマイページ。応募履歴と選考ステータスを表示する(SPEC.md 5.4)。
 */
class MyPageController extends Controller
{
    /** @var list<string> 「選考中」に数えるステータス。内定・不採用・辞退の手前まで。 */
    private const IN_PROGRESS_STATUSES = [
        Application::STATUS_NEW,
        Application::STATUS_DOCUMENT_SCREENING,
        Application::STATUS_INTERVIEW_ARRANGING,
        Application::STATUS_INTERVIEWED,
    ];

    /** @var list<string> 「内定」に数えるステータス。入社後も内定件数として数え続ける。 */
    private const OFFER_STATUSES = [
        Application::STATUS_OFFER,
        Application::STATUS_HIRED,
    ];

    public function __invoke(): View
    {
        $jobSeeker = auth('seeker')->user();

        $applications = $jobSeeker->applications()
            ->with(['jobPosting', 'jobPosting.company'])
            ->latest('applied_at')
            ->get();

        return view('seeker.mypage', [
            'applications' => $applications,
            'stats' => [
                'total' => $applications->count(),
                'in_progress' => $applications->whereIn('status', self::IN_PROGRESS_STATUSES)->count(),
                'offer' => $applications->whereIn('status', self::OFFER_STATUSES)->count(),
            ],
        ]);
    }
}
