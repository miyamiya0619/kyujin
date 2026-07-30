<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Services\ReviewJobPostingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * 求人の審査(運営者)。**運営者の日常業務の中心**(SPEC.md 7章)。
 *
 * 全掲載企業を横断して審査待ちの求人を扱うため、掲載企業のリソースと違い
 * URL に company を含めない。運営者はもともと全社の求人を扱える権限を持つ。
 */
class ReviewController extends Controller
{
    public function __construct(private readonly ReviewJobPostingService $reviewer) {}

    public function index(): View
    {
        return view('admin.reviews.index', [
            'jobPostings' => JobPosting::query()
                ->where('status', JobPosting::STATUS_PENDING)
                ->with('company', 'workplace')
                ->orderBy('updated_at')
                ->get(),
        ]);
    }

    public function approve(JobPosting $jobPosting): RedirectResponse
    {
        $this->reviewer->approve($jobPosting, auth('admin')->user());

        return redirect()->route('admin.reviews.index')
            ->with('status', "「{$jobPosting->title}」を承認し、公開しました。");
    }

    public function reject(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => '差戻し理由を入力してください。',
        ]);

        $this->reviewer->reject($jobPosting, auth('admin')->user(), $request->string('reason')->toString());

        return redirect()->route('admin.reviews.index')
            ->with('status', "「{$jobPosting->title}」を差戻しました。掲載企業に通知しました。");
    }
}
