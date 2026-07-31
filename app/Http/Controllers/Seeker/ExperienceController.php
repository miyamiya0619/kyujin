<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seeker\ExperienceRequest;
use App\Models\JobSeeker;
use App\Models\JobSeekerExperience;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 求職者の職務経歴 CRUD + 並び替え。
 * URL に求職者 ID を含めない。対象は常にログイン中の本人(CLAUDE.md 3.8 と同じ考え方)。
 */
class ExperienceController extends Controller
{
    public function store(ExperienceRequest $request): RedirectResponse
    {
        $jobSeeker = $this->jobSeeker();

        $nextOrder = ($jobSeeker->experiences()->max('sort_order') ?? 0) + 10;

        $jobSeeker->experiences()->create([
            ...$request->validated(),
            'sort_order' => $nextOrder,
        ]);

        return redirect()->route('seeker.profile.edit')->with('status', '職務経歴を追加しました。');
    }

    public function update(ExperienceRequest $request, JobSeekerExperience $experience): RedirectResponse
    {
        $this->ensureBelongsToMe($experience);

        $experience->update($request->validated());

        return redirect()->route('seeker.profile.edit')->with('status', '職務経歴を更新しました。');
    }

    public function destroy(JobSeekerExperience $experience): RedirectResponse
    {
        $this->ensureBelongsToMe($experience);

        $experience->delete();

        return redirect()->route('seeker.profile.edit')->with('status', '職務経歴を削除しました。');
    }

    /**
     * 並び替え。フロントから並び替え後の ID 配列を受け取り、
     * 10 刻みの sort_order を振り直す。
     */
    public function reorder(Request $request): RedirectResponse
    {
        $jobSeeker = $this->jobSeeker();
        $ids = array_map('intval', (array) $request->input('experience_ids', []));

        // 自分の職務経歴以外の ID が混ざっていても無視する(他人の経歴を操作させない)
        $myIds = $jobSeeker->experiences()->pluck('id')->all();

        $order = 0;
        foreach ($ids as $id) {
            if (! in_array($id, $myIds, true)) {
                continue;
            }

            $order += 10;
            JobSeekerExperience::whereKey($id)->update(['sort_order' => $order]);
        }

        return redirect()->route('seeker.profile.edit')->with('status', '並び順を更新しました。');
    }

    /** 403 ではなく 404(存在自体を知らせない)。 */
    private function ensureBelongsToMe(JobSeekerExperience $experience): void
    {
        if ($experience->job_seeker_id !== $this->jobSeeker()->id) {
            throw new NotFoundHttpException;
        }
    }

    private function jobSeeker(): JobSeeker
    {
        return auth('seeker')->user();
    }
}
