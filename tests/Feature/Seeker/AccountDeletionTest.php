<?php

namespace Tests\Feature\Seeker;

use App\Models\JobSeeker;
use App\Models\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_退会するとアカウントが削除されログアウトする(): void
    {
        $jobSeeker = JobSeeker::factory()->create();

        $this->actingAs($jobSeeker, 'seeker')
            ->delete(route('seeker.account.destroy'))
            ->assertRedirect(route('public.top'));

        $this->assertModelMissing($jobSeeker);
        $this->assertGuest('seeker');
    }

    public function test_退会すると保有資格と職務経歴も一緒に削除される(): void
    {
        $this->seed();

        $jobSeeker = JobSeeker::factory()->create();
        $jobSeeker->experiences()->create(['organization_name' => 'テスト事業所']);
        $qualificationId = Qualification::selectable()->first()->id;
        $jobSeeker->qualifications()->attach($qualificationId);

        $this->actingAs($jobSeeker, 'seeker')->delete(route('seeker.account.destroy'));

        $this->assertDatabaseMissing('job_seeker_experiences', ['job_seeker_id' => $jobSeeker->id]);
        $this->assertDatabaseMissing('job_seeker_qualification', ['job_seeker_id' => $jobSeeker->id]);
    }

    public function test_未ログインでは退会できない(): void
    {
        $this->delete(route('seeker.account.destroy'))->assertRedirect(route('seeker.login'));
    }
}
