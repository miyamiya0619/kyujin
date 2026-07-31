<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seeker\ApplyRequest;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Services\CreateApplicationService;
use App\Services\ReferrerSourceResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 求人への応募(TASKS.md T-12)。
 *
 * ログイン中の求職者はプロフィールから自動入力して応募するだけ。
 * 未ログインの場合はこの画面で会員登録と応募を同時に行う。
 */
class ApplicationController extends Controller
{
    public function create(JobPosting $jobPosting, ReferrerSourceResolver $referrer): View
    {
        $this->ensureApplyable($jobPosting, $referrer);

        return view('public.jobs.apply', [
            'jobPosting' => $jobPosting,
            'jobSeeker' => auth('seeker')->user(),
            'alreadyApplied' => $this->hasAlreadyApplied($jobPosting),
        ]);
    }

    public function store(
        ApplyRequest $request,
        JobPosting $jobPosting,
        CreateApplicationService $service,
        ReferrerSourceResolver $referrer
    ): RedirectResponse {
        $this->ensureApplyable($jobPosting, $referrer);

        /** @var JobSeeker|null $jobSeeker */
        $jobSeeker = auth('seeker')->user();

        if (! $jobSeeker) {
            $jobSeeker = JobSeeker::create([
                'name' => $request->string('name')->toString(),
                'name_kana' => $request->input('name_kana'),
                'email' => $request->string('email')->toString(),
                'password' => Hash::make($request->string('password')->toString()),
            ]);

            Auth::guard('seeker')->login($jobSeeker);
            $request->session()->regenerate();
        }

        $service->create(
            jobSeeker: $jobSeeker,
            jobPosting: $jobPosting,
            message: $request->input('message'),
            referrerSource: $referrer->resolve($request),
        );

        return redirect()->route('seeker.mypage')->with('status', '応募が完了しました。');
    }

    private function ensureApplyable(JobPosting $jobPosting, ReferrerSourceResolver $referrer): void
    {
        $referrer->capture(request());

        // 審査待ち・下書き・掲載終了の求人には応募させない(公開ページと同じ判定。CLAUDE.md 3.5)。
        if (! JobPosting::published()->whereKey($jobPosting->id)->exists()) {
            throw new NotFoundHttpException;
        }
    }

    private function hasAlreadyApplied(JobPosting $jobPosting): bool
    {
        $jobSeeker = auth('seeker')->user();

        if (! $jobSeeker) {
            return false;
        }

        return Application::where('job_posting_id', $jobPosting->id)
            ->where('job_seeker_id', $jobSeeker->id)
            ->exists();
    }
}
