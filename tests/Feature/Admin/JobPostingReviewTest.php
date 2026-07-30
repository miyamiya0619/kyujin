<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\JobPosting;
use App\Models\JobPostingReview;
use App\Models\SiteSetting;
use App\Notifications\JobPostingRejectedNotification;
use App\Services\ReviewJobPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * 運営者による求人審査(SPEC.md 7章)。**運営者の日常業務の中心。**
 */
class JobPostingReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_審査待ちの求人が一覧に表示される(): void
    {
        $admin = AdminUser::factory()->create();
        $pending = JobPosting::factory()->create(['status' => JobPosting::STATUS_PENDING, 'title' => '審査待ちの求人']);
        $draft = JobPosting::factory()->create(['status' => JobPosting::STATUS_DRAFT, 'title' => '下書きの求人']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('審査待ちの求人')
            ->assertDontSee('下書きの求人');
    }

    public function test_承認すると公開されて履歴が残る(): void
    {
        $admin = AdminUser::factory()->create();
        $jobPosting = JobPosting::factory()->create(['status' => JobPosting::STATUS_PENDING]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.job-postings.approve', $jobPosting))
            ->assertRedirect(route('admin.reviews.index'));

        $jobPosting->refresh();

        $this->assertSame(JobPosting::STATUS_PUBLISHED, $jobPosting->status);
        $this->assertNotNull($jobPosting->published_at);

        $this->assertDatabaseHas('job_posting_reviews', [
            'job_posting_id' => $jobPosting->id,
            'admin_user_id' => $admin->id,
            'action' => JobPostingReview::ACTION_APPROVED,
        ]);
    }

    public function test_差戻すと理由付きで記録され掲載企業に通知が届く(): void
    {
        Notification::fake();

        $admin = AdminUser::factory()->create();
        $company = Company::factory()->create();
        $companyUser = CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_PENDING]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.job-postings.reject', $jobPosting), ['reason' => '給与の記載が実態と異なります'])
            ->assertRedirect(route('admin.reviews.index'));

        $jobPosting->refresh();

        $this->assertSame(JobPosting::STATUS_REJECTED, $jobPosting->status);

        $this->assertDatabaseHas('job_posting_reviews', [
            'job_posting_id' => $jobPosting->id,
            'admin_user_id' => $admin->id,
            'action' => JobPostingReview::ACTION_REJECTED,
            'comment' => '給与の記載が実態と異なります',
        ]);

        Notification::assertSentTo($companyUser, JobPostingRejectedNotification::class);
    }

    public function test_差戻し理由が空だと差戻せない(): void
    {
        $admin = AdminUser::factory()->create();
        $jobPosting = JobPosting::factory()->create(['status' => JobPosting::STATUS_PENDING]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.job-postings.reject', $jobPosting), ['reason' => ''])
            ->assertSessionHasErrors('reason');

        $this->assertSame(JobPosting::STATUS_PENDING, $jobPosting->fresh()->status);
    }

    public function test_審査待ち以外の求人は承認できない(): void
    {
        $admin = AdminUser::factory()->create();
        $draft = JobPosting::factory()->create(['status' => JobPosting::STATUS_DRAFT]);

        $service = app(ReviewJobPostingService::class);

        $this->expectException(ValidationException::class);
        $service->approve($draft, $admin);
    }

    public function test_審査待ち以外の求人は差戻せない(): void
    {
        $admin = AdminUser::factory()->create();
        $published = JobPosting::factory()->create(['status' => JobPosting::STATUS_PUBLISHED]);

        $service = app(ReviewJobPostingService::class);

        $this->expectException(ValidationException::class);
        $service->reject($published, $admin, '理由');
    }

    public function test_掲載企業は審査待ち一覧に入れない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->get(route('admin.reviews.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_サイト設定で審査を_of_fにすると提出時に即公開される(): void
    {
        SiteSetting::current()->update(['requires_review' => false]);

        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_DRAFT]);

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.submit', $jobPosting))
            ->assertRedirect(route('company.job-postings.index'));

        $jobPosting->refresh();

        $this->assertSame(JobPosting::STATUS_PUBLISHED, $jobPosting->status);
        $this->assertDatabaseHas('job_posting_reviews', [
            'job_posting_id' => $jobPosting->id,
            'admin_user_id' => null,
            'action' => JobPostingReview::ACTION_AUTO_APPROVED,
        ]);
    }

    public function test_サイト設定で審査が_o_nのときは提出しても審査待ちになるだけ(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_DRAFT]);

        $this->actingAs($user, 'company')->post(route('company.job-postings.submit', $jobPosting));

        $this->assertSame(JobPosting::STATUS_PENDING, $jobPosting->fresh()->status);
        $this->assertDatabaseCount('job_posting_reviews', 0);
    }

    /**
     * 審査を通っていない求人が公開サイトに出ないことの最終確認。
     * CLAUDE.md 3.5 の絶対ルールが実際に機能していることを保証する。
     */
    public function test_公開中以外のあらゆるステータスがpublishedスコープから除外される(): void
    {
        foreach ([
            JobPosting::STATUS_DRAFT,
            JobPosting::STATUS_PENDING,
            JobPosting::STATUS_REJECTED,
            JobPosting::STATUS_CLOSED,
        ] as $status) {
            JobPosting::factory()->create(['status' => $status]);
        }

        JobPosting::factory()->create(['status' => JobPosting::STATUS_PUBLISHED]);

        $this->assertSame(1, JobPosting::published()->count());
    }
}
