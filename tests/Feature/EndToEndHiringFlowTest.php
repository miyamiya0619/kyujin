<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Application;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\EmploymentType;
use App\Models\JobCategory;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Models\Prefecture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T-19: セルフ入稿 → 審査 → 公開 → 検索露出 → 応募 → 選考管理までの一連を、
 * 実際の HTTP エンドポイントを通して確認する統合テスト。
 *
 * 個々の遷移は Company/JobPostingSubmitTest・Admin/JobPostingReviewTest・
 * Public/ApplicationTest 等ですでに検証済みだが、それらは単体の遷移しか見ていない。
 * ここでは「新規求人が実際に検索結果へ現れるか」「応募が企業側・求職者側の両方に
 * 正しく反映されるか」という、機能をつないだときにしか見えない継ぎ目を確認する。
 */
class EndToEndHiringFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_セルフ入稿から応募までの一連が実データで通る(): void
    {
        // 都道府県・職種・雇用形態などのマスタは DatabaseSeeder で投入される。
        $this->seed();

        // 運営者が掲載企業アカウントを発行した想定(T-05 で別途テスト済みのため前提として作成)。
        $company = Company::factory()->create(['name' => 'E2Eテスト介護株式会社']);
        $companyUser = CompanyUser::factory()->for($company)->owner()->create();

        $prefecture = Prefecture::query()->firstOrFail();
        $jobCategory = JobCategory::query()->firstOrFail();
        $employmentType = EmploymentType::query()->firstOrFail();

        // ---- 掲載企業: 事業所を登録する ----
        $this->actingAs($companyUser, 'company')
            ->post(route('company.workplaces.store'), [
                'name' => 'E2Eテストデイサービス',
                'prefecture_id' => $prefecture->id,
            ])
            ->assertRedirect();

        $workplace = $company->workplaces()->firstOrFail();

        // ---- 掲載企業: 求人をセルフ入稿する(下書き) ----
        $this->actingAs($companyUser, 'company')
            ->post(route('company.job-postings.store'), [
                'workplace_id' => $workplace->id,
                'title' => 'E2Eテスト求人・介護職員',
                'job_category_id' => $jobCategory->id,
                'employment_type_id' => $employmentType->id,
                'salary_type' => JobPosting::SALARY_TYPE_MONTHLY,
                'salary_min' => 220000,
                'salary_max' => 260000,
                'allow_external_feed' => true,
            ])
            ->assertRedirect();

        $jobPosting = $company->jobPostings()->firstOrFail();
        $this->assertSame(JobPosting::STATUS_DRAFT, $jobPosting->status, '入稿直後は下書きのはず');

        // ---- 掲載企業: 審査へ提出する ----
        $this->actingAs($companyUser, 'company')
            ->post(route('company.job-postings.submit', $jobPosting))
            ->assertRedirect();

        $this->assertSame(JobPosting::STATUS_PENDING, $jobPosting->fresh()->status);

        // 提出直後のリダイレクト先で完了メッセージが見える(セッションの flash は
        // 次の1リクエストだけ有効なので、ここで見ておかないと後続の公開サイトへの
        // アクセスで意図せず表示されてしまう)。
        $this->actingAs($companyUser, 'company')
            ->get(route('company.job-postings.index'))
            ->assertSee('を審査に提出しました');

        // 審査待ちの間は公開サイトに出てはいけない(CLAUDE.md 3.5)。
        $this->get(route('public.jobs.index'))->assertDontSee('E2Eテスト求人・介護職員');

        // ---- 運営者: 審査キューで見つけて承認する ----
        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('E2Eテスト求人・介護職員');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.job-postings.approve', $jobPosting))
            ->assertRedirect();

        $jobPosting->refresh();
        $this->assertSame(JobPosting::STATUS_PUBLISHED, $jobPosting->status);
        $this->assertNotNull($jobPosting->published_at);

        // ---- 公開サイト: 公開直後に検索結果へ出る ----
        $this->get(route('public.jobs.index'))->assertSee('E2Eテスト求人・介護職員');

        // ---- 求職者: 会員登録して、その場で応募する ----
        $this->post(route('seeker.register.store'), [
            'name' => 'E2E テスト太郎',
            'email' => 'e2e-seeker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('seeker.mypage'));

        $seeker = JobSeeker::query()->where('email', 'e2e-seeker@example.com')->firstOrFail();

        $this->actingAs($seeker, 'seeker')
            ->post(route('public.jobs.apply.store', $jobPosting), ['message' => 'ぜひ応募させてください。'])
            ->assertRedirect(route('seeker.mypage'));

        // ---- 掲載企業: 応募者一覧に見える ----
        // 一覧には求人名・事業所・応募日・流入元・ステータスまでしか出ず、
        // 応募者の氏名は詳細ページ(履歴書スナップショット)でしか分からない。
        // 実際に触って気づいた挙動(TASKS.md に改善タスクとして起票済み)。
        $this->actingAs($companyUser, 'company')
            ->get(route('company.applications.index'))
            ->assertOk()
            ->assertSee('E2Eテスト求人・介護職員');

        $application = Application::query()->where('job_posting_id', $jobPosting->id)->firstOrFail();

        $this->actingAs($companyUser, 'company')
            ->get(route('company.applications.show', $application))
            ->assertOk()
            ->assertSee('E2E テスト太郎');

        // ---- 求職者: マイページの応募履歴に見える ----
        $this->actingAs($seeker, 'seeker')
            ->get(route('seeker.mypage'))
            ->assertOk()
            ->assertSee('E2Eテスト求人・介護職員');
    }
}
