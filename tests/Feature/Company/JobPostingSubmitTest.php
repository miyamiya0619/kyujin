<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobPostingSubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_下書きの求人を審査に提出できる(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_DRAFT]);

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.submit', $jobPosting))
            ->assertRedirect(route('company.job-postings.index'));

        $this->assertSame(JobPosting::STATUS_PENDING, $jobPosting->fresh()->status);
    }

    public function test_差戻された求人を再提出できる(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_REJECTED]);

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.submit', $jobPosting));

        $this->assertSame(JobPosting::STATUS_PENDING, $jobPosting->fresh()->status);
    }

    public function test_審査待ちの求人は再提出できない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_PENDING]);

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.submit', $jobPosting))
            ->assertSessionHasErrors('status');

        $this->assertSame(JobPosting::STATUS_PENDING, $jobPosting->fresh()->status);
    }

    public function test_公開中の求人は提出できない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->create(['status' => JobPosting::STATUS_PUBLISHED]);

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.submit', $jobPosting))
            ->assertSessionHasErrors('status');
    }

    public function test_他社の求人を提出できない(): void
    {
        $company = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $otherJobPosting = JobPosting::factory()->for($other)->create(['status' => JobPosting::STATUS_DRAFT]);

        $this->actingAs($user, 'company')
            ->post(route('company.job-postings.submit', $otherJobPosting))
            ->assertNotFound();

        $this->assertSame(JobPosting::STATUS_DRAFT, $otherJobPosting->fresh()->status);
    }
}
