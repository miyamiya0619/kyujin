<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * メール認証(T-27)が済まないまま放置された仮登録の自動削除。
 */
class PurgeUnverifiedJobSeekersTest extends TestCase
{
    use RefreshDatabase;

    public function test_24時間放置された未認証の仮登録は削除される(): void
    {
        $stale = JobSeeker::factory()->unverified()->create(['created_at' => now()->subHours(25)]);

        $this->artisan('job-seekers:purge-unverified')->assertSuccessful();

        $this->assertDatabaseMissing('job_seekers', ['id' => $stale->id]);
    }

    public function test_削除後に同じメールアドレスで再登録できる(): void
    {
        JobSeeker::factory()->unverified()->create([
            'email' => 'hanako@example.com',
            'created_at' => now()->subHours(25),
        ]);

        $this->artisan('job-seekers:purge-unverified');

        $this->post(route('seeker.register.store'), [
            'name' => '山田花子',
            'email' => 'hanako@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('seeker.mypage'));

        $this->assertDatabaseHas('job_seekers', ['email' => 'hanako@example.com']);
    }

    public function test_24時間以内の未認証の仮登録は削除されない(): void
    {
        $fresh = JobSeeker::factory()->unverified()->create(['created_at' => now()->subHours(23)]);

        $this->artisan('job-seekers:purge-unverified');

        $this->assertDatabaseHas('job_seekers', ['id' => $fresh->id]);
    }

    public function test_認証済みの求職者は削除されない(): void
    {
        $verified = JobSeeker::factory()->create(['created_at' => now()->subDays(10)]);

        $this->artisan('job-seekers:purge-unverified');

        $this->assertDatabaseHas('job_seekers', ['id' => $verified->id]);
    }

    public function test_応募済みの未認証求職者は削除されない(): void
    {
        $jobPosting = JobPosting::factory()->published()->create();
        $applicant = JobSeeker::factory()->unverified()->create(['created_at' => now()->subHours(25)]);
        Application::factory()->for($jobPosting)->create(['job_seeker_id' => $applicant->id]);

        $this->artisan('job-seekers:purge-unverified');

        $this->assertDatabaseHas('job_seekers', ['id' => $applicant->id]);
    }
}
