<?php

namespace Tests\Feature\Auth;

use App\Models\AdminUser;
use App\Models\CompanyUser;
use App\Models\JobSeeker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 3 種類のログインが互いに侵食しないことを保証する。
 *
 * ここが破れると、掲載企業の担当者が運営管理画面に入って
 * 他社の応募者情報や審査機能に触れてしまう。
 */
class AuthenticationSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_未ログインでは各管理画面にアクセスできずログイン画面へ送られる(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/company')->assertRedirect(route('company.login'));
        $this->get('/mypage')->assertRedirect(route('seeker.login'));
    }

    public function test_掲載企業の担当者は運営管理画面に入れない(): void
    {
        $companyUser = CompanyUser::factory()->create();

        $this->actingAs($companyUser, 'company')
            ->get('/admin')
            ->assertRedirect(route('admin.login'));
    }

    public function test_求職者は掲載企業の管理画面に入れない(): void
    {
        $seeker = JobSeeker::factory()->create();

        $this->actingAs($seeker, 'seeker')
            ->get('/company')
            ->assertRedirect(route('company.login'));
    }

    public function test_求職者は運営管理画面に入れない(): void
    {
        $seeker = JobSeeker::factory()->create();

        $this->actingAs($seeker, 'seeker')
            ->get('/admin')
            ->assertRedirect(route('admin.login'));
    }

    public function test_運営者は掲載企業の管理画面に入れない(): void
    {
        // 運営者は最も強い権限を持つが、掲載企業のガードとは別物。
        // 掲載企業として振る舞う必要がある場合は Phase 2 のなりすまし機能で行う。
        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get('/company')
            ->assertRedirect(route('company.login'));
    }

    public function test_それぞれのガードで自分の画面には入れる(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin')
            ->get('/admin')->assertOk();

        $this->actingAs(CompanyUser::factory()->create(), 'company')
            ->get('/company')->assertOk();

        $this->actingAs(JobSeeker::factory()->create(), 'seeker')
            ->get('/mypage')->assertOk();
    }

    public function test_3つのガードに同時にログインできる(): void
    {
        // 運営者が動作確認するときに、同じブラウザで 3 つを行き来できるようにしている。
        $this->actingAs(AdminUser::factory()->create(), 'admin');
        $this->actingAs(CompanyUser::factory()->create(), 'company');
        $this->actingAs(JobSeeker::factory()->create(), 'seeker');

        $this->assertTrue(auth('admin')->check());
        $this->assertTrue(auth('company')->check());
        $this->assertTrue(auth('seeker')->check());
    }
}
