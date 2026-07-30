<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\EmploymentType;
use App\Models\JobFeature;
use App\Models\JobPosting;
use App\Models\Qualification;
use App\Models\Workplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 掲載企業によるセルフ入稿。
 * **URL に企業 ID を含めない設計**が事業所と同様に機能していることを確認する。
 */
class JobPostingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_自社の事業所に求人を登録すると下書きになる(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.store'), [
                'workplace_id' => $workplace->id,
                'title' => '介護職員募集',
            ])
            ->assertRedirect(route('company.job-postings.index'));

        $jobPosting = JobPosting::where('title', '介護職員募集')->firstOrFail();

        $this->assertSame($company->id, $jobPosting->company_id);
        $this->assertSame(JobPosting::STATUS_DRAFT, $jobPosting->status);
    }

    public function test_他社の事業所を指定すると求人を作れない(): void
    {
        $company = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $otherWorkplace = Workplace::factory()->for($other)->create();

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.store'), [
                'workplace_id' => $otherWorkplace->id,
                'title' => '不正な求人',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('job_postings', ['title' => '不正な求人']);
    }

    public function test_必要資格とこだわり条件を複数選択して保存できる(): void
    {
        $this->seed();

        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();

        $qualifications = Qualification::selectable()->take(2)->pluck('id');
        $features = JobFeature::selectable()->take(3)->pluck('id');

        $this->actingAs($user, 'company')->post(route('company.job-postings.store'), [
            'workplace_id' => $workplace->id,
            'title' => '資格条件つき求人',
            'qualification_ids' => $qualifications->all(),
            'feature_ids' => $features->all(),
        ]);

        $jobPosting = JobPosting::where('title', '資格条件つき求人')->firstOrFail();

        $this->assertSame($qualifications->sort()->values()->all(), $jobPosting->qualifications->pluck('id')->sort()->values()->all());
        $this->assertSame($features->sort()->values()->all(), $jobPosting->features->pluck('id')->sort()->values()->all());
    }

    public function test_編集画面で選択済みの資格が表示される(): void
    {
        $this->seed();

        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->for($workplace)->create();

        $qualification = Qualification::selectable()->firstOrFail();
        $jobPosting->qualifications()->attach($qualification->id);

        $response = $this->actingAs($user, 'company')->get(route('company.job-postings.edit', $jobPosting));

        $response->assertOk();
        $response->assertSee('name="qualification_ids[]" value="'.$qualification->id.'"', false);
    }

    public function test_他社の求人を編集できない(): void
    {
        $company = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $otherJobPosting = JobPosting::factory()->for($other)->create();

        $this->actingAs($user, 'company')
            ->get(route('company.job-postings.edit', $otherJobPosting))
            ->assertNotFound();
    }

    public function test_他社の求人を更新できない(): void
    {
        $company = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $otherJobPosting = JobPosting::factory()->for($other)->create(['title' => '元のタイトル']);

        $this->actingAs($user, 'company')
            ->put(route('company.job-postings.update', $otherJobPosting), [
                'workplace_id' => $otherJobPosting->workplace_id,
                'title' => '書き換え',
            ])
            ->assertNotFound();

        $this->assertSame('元のタイトル', $otherJobPosting->fresh()->title);
    }

    public function test_他社の求人を削除できない(): void
    {
        $company = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $otherJobPosting = JobPosting::factory()->for($other)->create();

        $this->actingAs($user, 'company')
            ->delete(route('company.job-postings.destroy', $otherJobPosting))
            ->assertNotFound();

        $this->assertModelExists($otherJobPosting);
    }

    public function test_他社の求人を複製できない(): void
    {
        $company = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $otherJobPosting = JobPosting::factory()->for($other)->create();

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.duplicate', $otherJobPosting))
            ->assertNotFound();
    }

    public function test_求人を複製すると全項目がコピーされた下書きが作られる(): void
    {
        $this->seed();

        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();
        $employmentType = EmploymentType::selectable()->firstOrFail();

        $original = JobPosting::factory()->for($company)->for($workplace)->published()->create([
            'title' => 'オリジナル求人',
            'employment_type_id' => $employmentType->id,
            'salary_min' => 200000,
            'salary_max' => 250000,
            'has_night_shift' => true,
        ]);

        $qualification = Qualification::selectable()->firstOrFail();
        $feature = JobFeature::selectable()->firstOrFail();
        $original->qualifications()->attach($qualification->id);
        $original->features()->attach($feature->id);

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.duplicate', $original))
            ->assertRedirect(route('company.job-postings.index'));

        $duplicate = JobPosting::where('title', 'オリジナル求人(コピー)')->firstOrFail();

        $this->assertSame(JobPosting::STATUS_DRAFT, $duplicate->status, '複製は下書きになること');
        $this->assertNull($duplicate->published_at);
        $this->assertSame($employmentType->id, $duplicate->employment_type_id);
        $this->assertSame(200000, $duplicate->salary_min);
        $this->assertSame(250000, $duplicate->salary_max);
        $this->assertTrue($duplicate->has_night_shift);
        $this->assertSame([$qualification->id], $duplicate->qualifications->pluck('id')->all());
        $this->assertSame([$feature->id], $duplicate->features->pluck('id')->all());

        // 元の求人は変わらず公開中のまま
        $this->assertSame(JobPosting::STATUS_PUBLISHED, $original->fresh()->status);
    }

    public function test_下書きは一覧に表示されるが公開サイトには出ない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();
        JobPosting::factory()->for($company)->for($workplace)->create(['title' => '下書き求人']);

        $this->actingAs($user, 'company')
            ->get(route('company.job-postings.index'))
            ->assertOk()
            ->assertSee('下書き求人');

        $this->assertSame(0, JobPosting::published()->count(), '下書きは published スコープに含まれない');
    }

    public function test_公開中の求人を編集すると審査待ちに戻る(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->for($workplace)->published()->create();

        $this->actingAs($user, 'company')->put(route('company.job-postings.update', $jobPosting), [
            'workplace_id' => $workplace->id,
            'title' => '内容を修正しました',
        ]);

        $this->assertSame(JobPosting::STATUS_PENDING, $jobPosting->fresh()->status);
    }

    public function test_下書きの求人を編集しても審査待ちにはならない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->for($workplace)->create();

        $this->actingAs($user, 'company')->put(route('company.job-postings.update', $jobPosting), [
            'workplace_id' => $workplace->id,
            'title' => '編集後のタイトル',
        ]);

        $this->assertSame(JobPosting::STATUS_DRAFT, $jobPosting->fresh()->status);
    }

    public function test_給与下限が上限を超えると保存できない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();

        $this->actingAs($user, 'company')->post(route('company.job-postings.store'), [
            'workplace_id' => $workplace->id,
            'title' => '給与不整合求人',
            'salary_min' => 300000,
            'salary_max' => 200000,
        ])->assertSessionHasErrors('salary_min');

        $this->assertDatabaseMissing('job_postings', ['title' => '給与不整合求人']);
    }

    public function test_一覧には自社の求人だけが表示される(): void
    {
        $mine = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($mine)->create();

        JobPosting::factory()->for($mine)->create(['title' => '自社求人']);
        JobPosting::factory()->for($other)->create(['title' => '他社求人']);

        $this->actingAs($user, 'company')
            ->get(route('company.job-postings.index'))
            ->assertOk()
            ->assertSee('自社求人')
            ->assertDontSee('他社求人');
    }
}
