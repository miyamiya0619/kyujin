<?php

namespace Tests\Feature\Auth;

use App\Models\AdminUser;
use App\Models\CompanyUser;
use App\Models\JobSeeker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 試行回数の制限はテスト間で持ち越さない
        RateLimiter::clear('admin|admin@example.com|127.0.0.1');
    }

    public function test_運営者がログインしてログアウトできる(): void
    {
        $admin = AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->get(route('admin.login'))->assertOk();

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_掲載企業の担当者がログインできる(): void
    {
        $user = CompanyUser::factory()->create([
            'email' => 'company@example.com',
            'password' => 'password',
        ]);

        $this->post(route('company.login.store'), [
            'email' => 'company@example.com',
            'password' => 'password',
        ])->assertRedirect(route('company.dashboard'));

        $this->assertAuthenticatedAs($user, 'company');
    }

    public function test_求職者がログインできる(): void
    {
        $seeker = JobSeeker::factory()->create([
            'email' => 'seeker@example.com',
            'password' => 'password',
        ]);

        $this->post(route('seeker.login.store'), [
            'email' => 'seeker@example.com',
            'password' => 'password',
        ])->assertRedirect(route('seeker.mypage'));

        $this->assertAuthenticatedAs($seeker, 'seeker');
    }

    public function test_パスワードが違えばログインできない(): void
    {
        AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_無効化された運営者はログインできない(): void
    {
        AdminUser::factory()->inactive()->create([
            'email' => 'retired@example.com',
            'password' => 'password',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'retired@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_無効化された掲載企業の担当者はログインできない(): void
    {
        CompanyUser::factory()->inactive()->create([
            'email' => 'retired@example.com',
            'password' => 'password',
        ]);

        $this->post(route('company.login.store'), [
            'email' => 'retired@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('company');
    }

    public function test_別のガードの認証情報ではログインできない(): void
    {
        // 求職者のメールアドレスとパスワードで運営管理画面に入れてはいけない
        JobSeeker::factory()->create([
            'email' => 'seeker@example.com',
            'password' => 'password',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'seeker@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_ログアウトしても他のガードのログイン状態は維持される(): void
    {
        $admin = AdminUser::factory()->create();
        $seeker = JobSeeker::factory()->create();

        $this->actingAs($admin, 'admin');
        $this->actingAs($seeker, 'seeker');

        $this->post(route('admin.logout'));

        $this->assertGuest('admin');
        $this->assertAuthenticatedAs($seeker, 'seeker');
    }

    public function test_ログイン済みならログイン画面から自分の画面へ送られる(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin')
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_試行回数の上限を超えるとロックされる(): void
    {
        AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.store'), [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6 回目は正しいパスワードでも弾かれる
        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }
}
