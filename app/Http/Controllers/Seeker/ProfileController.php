<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seeker\ProfileRequest;
use App\Models\City;
use App\Models\JobSeeker;
use App\Models\Prefecture;
use App\Models\Qualification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 求職者のプロフィール編集(基本情報 + 保有資格)。
 * 対象は常にログイン中の本人(URL に ID を含めない。CLAUDE.md 3.8 と同じ考え方)。
 */
class ProfileController extends Controller
{
    public function edit(): View
    {
        $jobSeeker = $this->jobSeeker();

        return view('seeker.profile.edit', [
            'jobSeeker' => $jobSeeker,
            'prefectures' => Prefecture::selectable()->get(),
            'cities' => $jobSeeker->prefecture_id
                ? City::selectable()->where('prefecture_id', $jobSeeker->prefecture_id)->get()
                : collect(),
            'qualifications' => Qualification::selectable()->get(),
            'selectedQualificationIds' => $jobSeeker->qualifications()->pluck('qualifications.id')->all(),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $jobSeeker = $this->jobSeeker();

        $jobSeeker->fill($request->safe()->except('qualification_ids'));
        $jobSeeker->save();

        $jobSeeker->qualifications()->sync($request->input('qualification_ids', []));

        return redirect()->route('seeker.profile.edit')->with('status', 'プロフィールを更新しました。');
    }

    private function jobSeeker(): JobSeeker
    {
        return auth('seeker')->user();
    }
}
