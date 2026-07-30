<?php

namespace Tests\Feature\Auth;

use App\Models\AdminUser;
use App\Models\JobSeeker;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_運営者がパスワードを再設定できる(): void
    {
        Notification::fake();

        $admin = AdminUser::factory()->create(['email' => 'admin@example.com']);

        $this->post(route('admin.password.email'), ['email' => 'admin@example.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo($admin, ResetPasswordNotification::class, function ($notification) use ($admin) {
            $this->post(route('admin.password.update'), [
                'token' => $this->tokenOf($notification),
                'email' => $admin->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])->assertRedirect(route('admin.login'));

            return true;
        });

        $this->assertTrue(Hash::check('new-password-123', $admin->fresh()->password));
    }

    /**
     * トークンテーブルをユーザー種別ごとに分けた効果を確かめる。
     * 共有していると、あとから申請したほうが先の申請のトークンを上書きしてしまう。
     */
    public function test_同じメールアドレスの運営者と求職者が互いの再設定を壊さない(): void
    {
        Notification::fake();

        $admin = AdminUser::factory()->create(['email' => 'same@example.com']);
        $seeker = JobSeeker::factory()->create(['email' => 'same@example.com']);

        $this->post(route('admin.password.email'), ['email' => 'same@example.com']);
        $this->post(route('seeker.password.email'), ['email' => 'same@example.com']);

        // 双方にトークンが残っていること
        $this->assertDatabaseCount('admin_password_reset_tokens', 1);
        $this->assertDatabaseCount('job_seeker_password_reset_tokens', 1);

        Notification::assertSentTo($admin, ResetPasswordNotification::class);
        Notification::assertSentTo($seeker, ResetPasswordNotification::class);
    }

    public function test_登録されていないメールでも同じ応答を返す(): void
    {
        // 応答を変えると、誰が登録しているかを外部から調べられてしまう
        $this->post(route('admin.password.email'), ['email' => 'unknown@example.com'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();
    }

    public function test_不正なトークンでは再設定できない(): void
    {
        $admin = AdminUser::factory()->create(['email' => 'admin@example.com']);

        $this->post(route('admin.password.update'), [
            'token' => 'invalid-token',
            'email' => $admin->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('email');
    }

    /**
     * 通知オブジェクトから token を取り出す(private プロパティのため)。
     */
    private function tokenOf(ResetPasswordNotification $notification): string
    {
        $reflection = new \ReflectionProperty($notification, 'token');

        return $reflection->getValue($notification);
    }
}
