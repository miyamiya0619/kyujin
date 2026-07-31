<?php

namespace Tests\Feature\Company;

use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\ApplicationResumeSnapshot;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\JobPosting;
use App\Models\Workplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 応募者管理(掲載企業。TASKS.md T-13)。
 *
 * **URL に企業 ID を含めない設計**(CLAUDE.md 3.8)が実際に機能していることを確認する。
 * 一覧・詳細・ステータス変更・メモ・CSV の全てで他社の応募者に 404 が返ることが完了条件。
 */
class ApplicationManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeApplication(Company $company, array $attributes = []): Application
    {
        $jobPosting = JobPosting::factory()->for($company)->published()->create();

        return Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'company_id' => $company->id,
            ...$attributes,
        ]);
    }

    public function test_自社の応募者一覧が表示される(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $other = Company::factory()->create();

        $mine = $this->makeApplication($company);
        $this->makeApplication($other);

        $response = $this->actingAs($user, 'company')->get(route('company.applications.index'));

        $response->assertOk()->assertSee($mine->jobPosting->title);
    }

    public function test_他社の応募者は一覧に出ない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $other = Company::factory()->create();

        $otherApplication = $this->makeApplication($other, []);
        $otherApplication->jobPosting->update(['title' => '他社だけの求人タイトル']);

        $this->actingAs($user, 'company')
            ->get(route('company.applications.index'))
            ->assertDontSee('他社だけの求人タイトル');
    }

    public function test_未対応の件数が表示される(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $this->makeApplication($company, ['status' => Application::STATUS_NEW]);
        $this->makeApplication($company, ['status' => Application::STATUS_NEW]);
        $this->makeApplication($company, ['status' => Application::STATUS_HIRED]);

        $this->actingAs($user, 'company')
            ->get(route('company.applications.index'))
            ->assertSee('未対応 2');
    }

    public function test_ステータスで絞り込める(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $new = $this->makeApplication($company, ['status' => Application::STATUS_NEW]);
        $hired = $this->makeApplication($company, ['status' => Application::STATUS_HIRED]);

        // 絞り込みフォームの求人・事業所セレクトには自社の全求人が選択肢として出るため、
        // 除外側の判定はタイトル文字列ではなく詳細リンクの有無で見る。
        $this->actingAs($user, 'company')
            ->get(route('company.applications.index', ['status' => Application::STATUS_NEW]))
            ->assertSee(route('company.applications.show', $new))
            ->assertDontSee(route('company.applications.show', $hired));
    }

    public function test_事業所で絞り込める(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $workplaceA = Workplace::factory()->for($company)->create(['name' => '事業所A']);
        $workplaceB = Workplace::factory()->for($company)->create(['name' => '事業所B']);

        $jobA = JobPosting::factory()->for($company)->for($workplaceA)->published()->create(['title' => '事業所A求人']);
        $jobB = JobPosting::factory()->for($company)->for($workplaceB)->published()->create(['title' => '事業所B求人']);

        $applicationA = Application::factory()->create(['job_posting_id' => $jobA->id, 'company_id' => $company->id]);
        $applicationB = Application::factory()->create(['job_posting_id' => $jobB->id, 'company_id' => $company->id]);

        $this->actingAs($user, 'company')
            ->get(route('company.applications.index', ['workplace_id' => $workplaceA->id]))
            ->assertSee(route('company.applications.show', $applicationA))
            ->assertDontSee(route('company.applications.show', $applicationB));
    }

    public function test_応募日で絞り込める(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $old = $this->makeApplication($company, ['applied_at' => '2026-01-01']);
        $recent = $this->makeApplication($company, ['applied_at' => '2026-07-01']);

        $this->actingAs($user, 'company')
            ->get(route('company.applications.index', ['applied_from' => '2026-06-01']))
            ->assertSee(route('company.applications.show', $recent))
            ->assertDontSee(route('company.applications.show', $old));
    }

    public function test_流入元で絞り込める(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $direct = $this->makeApplication($company, ['referrer_source' => 'direct']);
        $indeed = $this->makeApplication($company, ['referrer_source' => 'indeed']);

        $this->actingAs($user, 'company')
            ->get(route('company.applications.index', ['referrer_source' => 'indeed']))
            ->assertSee(route('company.applications.show', $indeed))
            ->assertDontSee(route('company.applications.show', $direct));
    }

    public function test_応募者詳細でスナップショットが表示される(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $application = $this->makeApplication($company);

        ApplicationResumeSnapshot::create([
            'application_id' => $application->id,
            'payload' => ['name' => '応募時の氏名', 'email' => 'applicant@example.com'],
            'snapshot_at' => now(),
        ]);

        $this->actingAs($user, 'company')
            ->get(route('company.applications.show', $application))
            ->assertOk()
            ->assertSee('応募時の氏名')
            ->assertSee('applicant@example.com');
    }

    public function test_他社の応募者詳細は_404になる(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $other = Company::factory()->create();
        $otherApplication = $this->makeApplication($other);

        $this->actingAs($user, 'company')
            ->get(route('company.applications.show', $otherApplication))
            ->assertNotFound();
    }

    public function test_選考ステータスを変更でき履歴が記録される(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $application = $this->makeApplication($company, ['status' => Application::STATUS_NEW]);

        $this->actingAs($user, 'company')
            ->put(route('company.applications.status.update', $application), ['status' => Application::STATUS_DOCUMENT_SCREENING])
            ->assertRedirect(route('company.applications.show', $application));

        $this->actingAs($user, 'company')
            ->put(route('company.applications.status.update', $application), ['status' => Application::STATUS_REJECTED]);

        $this->assertSame(Application::STATUS_REJECTED, $application->fresh()->status);

        $this->assertDatabaseHas('application_status_logs', [
            'application_id' => $application->id,
            'company_user_id' => $user->id,
            'from_status' => Application::STATUS_NEW,
            'to_status' => Application::STATUS_DOCUMENT_SCREENING,
        ]);
        $this->assertDatabaseHas('application_status_logs', [
            'application_id' => $application->id,
            'from_status' => Application::STATUS_DOCUMENT_SCREENING,
            'to_status' => Application::STATUS_REJECTED,
        ]);
    }

    public function test_不採用にした応募者も一覧に残る(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $application = $this->makeApplication($company, ['status' => Application::STATUS_NEW]);

        $this->actingAs($user, 'company')
            ->put(route('company.applications.status.update', $application), ['status' => Application::STATUS_REJECTED]);

        $this->actingAs($user, 'company')
            ->get(route('company.applications.index'))
            ->assertSee($application->jobPosting->title);

        $this->assertModelExists($application);
    }

    public function test_他社の応募者のステータスは変更できない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $other = Company::factory()->create();
        $otherApplication = $this->makeApplication($other, ['status' => Application::STATUS_NEW]);

        $this->actingAs($user, 'company')
            ->put(route('company.applications.status.update', $otherApplication), ['status' => Application::STATUS_HIRED])
            ->assertNotFound();

        $this->assertSame(Application::STATUS_NEW, $otherApplication->fresh()->status);
    }

    public function test_メモを追加できる(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $application = $this->makeApplication($company);

        $this->actingAs($user, 'company')
            ->post(route('company.applications.notes.store', $application), ['body' => '面接調整中、来週電話予定'])
            ->assertRedirect(route('company.applications.show', $application));

        $this->assertDatabaseHas('application_notes', [
            'application_id' => $application->id,
            'company_user_id' => $user->id,
            'body' => '面接調整中、来週電話予定',
        ]);
    }

    public function test_メモを削除できる(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $application = $this->makeApplication($company);
        $note = ApplicationNote::create([
            'application_id' => $application->id,
            'company_user_id' => $user->id,
            'body' => '削除対象のメモ',
        ]);

        $this->actingAs($user, 'company')
            ->delete(route('company.applications.notes.destroy', [$application, $note]))
            ->assertRedirect(route('company.applications.show', $application));

        $this->assertModelMissing($note);
    }

    public function test_他社の応募者にはメモを追加できない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $other = Company::factory()->create();
        $otherApplication = $this->makeApplication($other);

        $this->actingAs($user, 'company')
            ->post(route('company.applications.notes.store', $otherApplication), ['body' => '不正なメモ'])
            ->assertNotFound();

        $this->assertDatabaseMissing('application_notes', ['body' => '不正なメモ']);
    }

    public function test_他社の応募者の他社のメモは削除できない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $other = Company::factory()->create();
        $otherApplication = $this->makeApplication($other);
        $otherNote = ApplicationNote::create([
            'application_id' => $otherApplication->id,
            'company_user_id' => null,
            'body' => '他社のメモ',
        ]);

        $this->actingAs($user, 'company')
            ->delete(route('company.applications.notes.destroy', [$otherApplication, $otherNote]))
            ->assertNotFound();

        $this->assertModelExists($otherNote);
    }

    public function test_c_s_v出力が動作し監査ログに記録される(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $application = $this->makeApplication($company);

        ApplicationResumeSnapshot::create([
            'application_id' => $application->id,
            'payload' => ['name' => 'CSV太郎', 'email' => 'csv@example.com', 'tel' => '090-1111-2222'],
            'snapshot_at' => now(),
        ]);

        $response = $this->actingAs($user, 'company')->get(route('company.applications.export'));

        $response->assertOk();
        $this->assertStringContainsString('CSV太郎', $response->getContent());
        $this->assertStringContainsString('csv@example.com', $response->getContent());

        $this->assertDatabaseHas('audit_logs', [
            'actor_type' => 'company',
            'actor_id' => $user->id,
            'action' => 'applications.export_csv',
        ]);
    }
}
