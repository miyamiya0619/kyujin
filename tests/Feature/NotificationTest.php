<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Application;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Models\SiteSetting;
use App\Notifications\ApplicationReceivedNotification;
use App\Notifications\ApplicationStatusChangedNotification;
use App\Notifications\JobPostingApprovedNotification;
use App\Notifications\JobPostingRejectedNotification;
use App\Notifications\NewApplicationNotification;
use App\Notifications\NewReviewPendingNotification;
use App\Services\ChangeApplicationStatusService;
use App\Services\CreateApplicationService;
use App\Services\ReviewJobPostingService;
use App\Services\SubmitJobPostingForReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * 応募・審査の動きに応じたメール通知(TASKS.md T-15)。
 *
 * 全て `ShouldQueue` を実装しキュー経由で送る。ここでは `Notification::fake()` で
 * 送信先と種類だけを検証し、実際のキュー処理(cron 実行前提)は
 * `QueueWorkerTest` で確認する。
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_応募すると求職者と掲載企業の担当者に通知される(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $activeUser = CompanyUser::factory()->for($company)->create(['is_active' => true]);
        $inactiveUser = CompanyUser::factory()->for($company)->create(['is_active' => false]);
        $jobPosting = JobPosting::factory()->for($company)->published()->create();
        $jobSeeker = JobSeeker::factory()->create();

        $application = app(CreateApplicationService::class)->create($jobSeeker, $jobPosting, null, 'direct');

        Notification::assertSentTo($jobSeeker, ApplicationReceivedNotification::class);
        Notification::assertSentTo($activeUser, NewApplicationNotification::class);
        Notification::assertNotSentTo($inactiveUser, NewApplicationNotification::class);

        // 参照する応募が正しいことも確認する
        $siteName = SiteSetting::current()->site_name;
        Notification::assertSentTo($activeUser, NewApplicationNotification::class, function ($notification) use ($application, $siteName) {
            return $notification->toMail($application->jobSeeker)->subject === "【{$siteName}】求人「{$application->jobPosting->title}」に応募がありました";
        });
    }

    public function test_審査に提出すると有効な運営者にだけ通知される(): void
    {
        Notification::fake();

        $activeAdmin = AdminUser::factory()->create(['is_active' => true]);
        $inactiveAdmin = AdminUser::factory()->create(['is_active' => false]);

        $company = Company::factory()->create();
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_DRAFT]);

        app(SubmitJobPostingForReviewService::class)->submit($jobPosting);

        Notification::assertSentTo($activeAdmin, NewReviewPendingNotification::class);
        Notification::assertNotSentTo($inactiveAdmin, NewReviewPendingNotification::class);
    }

    public function test_審査off設定では審査待ちにならないため運営者に通知されない(): void
    {
        Notification::fake();

        SiteSetting::current()->update(['requires_review' => false]);

        $admin = AdminUser::factory()->create();
        $company = Company::factory()->create();
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_DRAFT]);

        app(SubmitJobPostingForReviewService::class)->submit($jobPosting);

        Notification::assertNotSentTo($admin, NewReviewPendingNotification::class);
    }

    public function test_承認すると有効な担当者にだけ通知される(): void
    {
        Notification::fake();

        $admin = AdminUser::factory()->create();
        $company = Company::factory()->create();
        $activeUser = CompanyUser::factory()->for($company)->create(['is_active' => true]);
        $inactiveUser = CompanyUser::factory()->for($company)->create(['is_active' => false]);
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_PENDING]);

        app(ReviewJobPostingService::class)->approve($jobPosting, $admin);

        Notification::assertSentTo($activeUser, JobPostingApprovedNotification::class);
        Notification::assertNotSentTo($inactiveUser, JobPostingApprovedNotification::class);
    }

    public function test_差戻すと担当者に通知される(): void
    {
        Notification::fake();

        $admin = AdminUser::factory()->create();
        $company = Company::factory()->create();
        $companyUser = CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_PENDING]);

        app(ReviewJobPostingService::class)->reject($jobPosting, $admin, '理由');

        Notification::assertSentTo($companyUser, JobPostingRejectedNotification::class);
    }

    public function test_選考ステータスが変わると求職者に通知される(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $companyUser = CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->published()->create();
        $jobSeeker = JobSeeker::factory()->create();
        $application = Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'job_seeker_id' => $jobSeeker->id,
            'company_id' => $company->id,
            'status' => Application::STATUS_NEW,
        ]);

        app(ChangeApplicationStatusService::class)->change($application, Application::STATUS_DOCUMENT_SCREENING, $companyUser);

        Notification::assertSentTo($jobSeeker, ApplicationStatusChangedNotification::class);
    }

    public function test_同じステータスに変更しても通知されない(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $companyUser = CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->published()->create();
        $jobSeeker = JobSeeker::factory()->create();
        $application = Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'job_seeker_id' => $jobSeeker->id,
            'company_id' => $company->id,
            'status' => Application::STATUS_NEW,
        ]);

        app(ChangeApplicationStatusService::class)->change($application, Application::STATUS_NEW, $companyUser);

        Notification::assertNotSentTo($jobSeeker, ApplicationStatusChangedNotification::class);
    }
}
