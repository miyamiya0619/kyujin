<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationResumeSnapshot;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * 求職者が求人に応募する(SPEC.md 8.2 / TASKS.md T-12)。
 *
 * その時点の履歴書内容を JSON でスナップショット化して保存する。
 * 応募後に求職者がプロフィールを変更してもスナップショットは変わらない
 * (選考の公平性と証跡。CLAUDE.md 3.6)。
 */
class CreateApplicationService
{
    public function create(JobSeeker $jobSeeker, JobPosting $jobPosting, ?string $message, string $referrerSource): Application
    {
        if (Application::where('job_posting_id', $jobPosting->id)->where('job_seeker_id', $jobSeeker->id)->exists()) {
            throw ValidationException::withMessages([
                'application' => 'この求人には既に応募済みです。',
            ]);
        }

        return DB::transaction(function () use ($jobSeeker, $jobPosting, $message, $referrerSource) {
            $application = Application::create([
                'job_posting_id' => $jobPosting->id,
                'job_seeker_id' => $jobSeeker->id,
                'company_id' => $jobPosting->company_id,
                'status' => Application::STATUS_NEW,
                'message' => $message,
                'referrer_source' => $referrerSource,
                'applied_at' => now(),
            ]);

            ApplicationResumeSnapshot::create([
                'application_id' => $application->id,
                'payload' => $this->buildResumeSnapshot($jobSeeker),
                'snapshot_at' => now(),
            ]);

            DB::table('job_postings')->where('id', $jobPosting->id)->increment('application_count');

            return $application;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildResumeSnapshot(JobSeeker $jobSeeker): array
    {
        $jobSeeker->loadMissing(['prefecture', 'city', 'qualifications', 'experiences']);

        return [
            'name' => $jobSeeker->name,
            'name_kana' => $jobSeeker->name_kana,
            'email' => $jobSeeker->email,
            'tel' => $jobSeeker->tel,
            'birthday' => $jobSeeker->birthday?->toDateString(),
            'prefecture' => $jobSeeker->prefecture?->name,
            'city' => $jobSeeker->city?->name,
            'qualifications' => $jobSeeker->qualifications->pluck('name')->all(),
            'experiences' => $jobSeeker->experiences->map(fn ($experience) => [
                'organization_name' => $experience->organization_name,
                'job_title' => $experience->job_title,
                'started_on' => $experience->started_on?->toDateString(),
                'ended_on' => $experience->ended_on?->toDateString(),
                'description' => $experience->description,
            ])->all(),
        ];
    }
}
