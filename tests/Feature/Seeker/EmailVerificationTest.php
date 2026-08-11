<?php

namespace Tests\Feature\Seeker;

use App\Models\JobPosting;
use App\Models\JobSeeker;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * 求職者会員登録のメール認証(TASKS.md T-27)。
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_会員登録すると認証メールが送られマイページには入れない(): void
    {
        Notification::fake();

        $this->post(route('seeker.register.store'), [
            'name' => '山田花子',
            'email' => 'hanako@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('seeker.mypage'));

        $jobSeeker = JobSeeker::where('email', 'hanako@example.com')->firstOrFail();

        $this->assertNull($jobSeeker->email_verified_at);
        Notification::assertSentTo($jobSeeker, VerifyEmail::class);

        // マイページへは verified ミドルウェアに止められ、認証案内画面へ送られる。
        $this->actingAs($jobSeeker, 'seeker')
            ->get(route('seeker.mypage'))
            ->assertRedirect(route('seeker.verification.notice'));

        $this->actingAs($jobSeeker, 'seeker')
            ->get(route('seeker.verification.notice'))
            ->assertOk();
    }

    public function test_認証リンクを踏むと本登録が完了しマイページに入れる(): void
    {
        $jobSeeker = JobSeeker::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('seeker.verification.verify', now()->addHours(24), [
            'id' => $jobSeeker->id,
            'hash' => sha1($jobSeeker->email),
        ]);

        $this->actingAs($jobSeeker, 'seeker')
            ->get($url)
            ->assertRedirect(route('seeker.mypage'));

        $this->assertNotNull($jobSeeker->fresh()->email_verified_at);

        $this->actingAs($jobSeeker, 'seeker')
            ->get(route('seeker.mypage'))
            ->assertOk();
    }

    public function test_有効期限切れの認証リンクでは認証できない(): void
    {
        $jobSeeker = JobSeeker::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('seeker.verification.verify', now()->subHour(), [
            'id' => $jobSeeker->id,
            'hash' => sha1($jobSeeker->email),
        ]);

        $this->actingAs($jobSeeker, 'seeker')
            ->get($url)
            ->assertForbidden();

        $this->assertNull($jobSeeker->fresh()->email_verified_at);
    }

    public function test_他人の認証リンクでは認証できない(): void
    {
        $jobSeeker = JobSeeker::factory()->unverified()->create();
        $other = JobSeeker::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('seeker.verification.verify', now()->addHours(24), [
            'id' => $other->id,
            'hash' => sha1($other->email),
        ]);

        $this->actingAs($jobSeeker, 'seeker')
            ->get($url)
            ->assertForbidden();

        $this->assertNull($other->fresh()->email_verified_at);
    }

    public function test_認証メールを再送できる(): void
    {
        Notification::fake();

        $jobSeeker = JobSeeker::factory()->unverified()->create();

        $this->actingAs($jobSeeker, 'seeker')
            ->post(route('seeker.verification.send'))
            ->assertRedirect();

        Notification::assertSentTo($jobSeeker, VerifyEmail::class);
    }

    public function test_未認証でも応募自体は完了する(): void
    {
        Notification::fake();

        $jobPosting = JobPosting::factory()->published()->create();

        $this->post(route('public.jobs.apply.store', $jobPosting), [
            'name' => '未認証太郎',
            'email' => 'mikensho@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'message' => 'よろしくお願いします。',
        ])->assertRedirect(route('seeker.mypage'));

        $jobSeeker = JobSeeker::where('email', 'mikensho@example.com')->firstOrFail();

        $this->assertNull($jobSeeker->email_verified_at, '未認証のまま応募できる');
        $this->assertDatabaseHas('applications', [
            'job_posting_id' => $jobPosting->id,
            'job_seeker_id' => $jobSeeker->id,
        ]);

        // 応募自体は完了しているが、マイページ(応募履歴の確認)は認証待ちに止まる。
        $this->actingAs($jobSeeker, 'seeker')
            ->get(route('seeker.mypage'))
            ->assertRedirect(route('seeker.verification.notice'));
    }

    public function test_未認証でも退会できる(): void
    {
        $jobSeeker = JobSeeker::factory()->unverified()->create();

        $this->actingAs($jobSeeker, 'seeker')
            ->delete(route('seeker.account.destroy'))
            ->assertRedirect();

        $this->assertDatabaseMissing('job_seekers', ['id' => $jobSeeker->id]);
    }
}
