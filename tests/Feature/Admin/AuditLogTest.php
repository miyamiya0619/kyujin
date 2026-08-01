<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\AuditLog;
use App\Models\CompanyUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 監査ログの閲覧(運営者。TASKS.md T-16)。
 */
class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_監査ログの一覧が表示される(): void
    {
        $admin = AdminUser::factory()->create();
        AuditLog::record('admin', $admin->id, 'site_settings.update');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('site_settings.update');
    }

    public function test_操作者の種別で絞り込める(): void
    {
        $admin = AdminUser::factory()->create();
        AuditLog::record('admin', $admin->id, 'site_settings.update');
        AuditLog::record('company', 1, 'applications.export_csv');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.audit-logs.index', ['actor_type' => 'company']));

        $response->assertSee('applications.export_csv');

        // 絞り込みフォームの操作セレクトには全アクションが選択肢として出るため、
        // 除外側の判定は一覧の行のマークアップそのものが無いことで見る。
        $response->assertDontSee('<td class="px-4 py-3 font-mono text-xs">site_settings.update</td>', false);
    }

    public function test_期間で絞り込める(): void
    {
        $admin = AdminUser::factory()->create();

        $old = AuditLog::record('admin', $admin->id, 'old_action');
        $old->forceFill(['created_at' => now()->subMonth()])->save();

        AuditLog::record('admin', $admin->id, 'recent_action');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.audit-logs.index', ['from' => now()->subWeek()->toDateString()]));

        $response->assertSee('recent_action');
        $response->assertDontSee('<td class="px-4 py-3 font-mono text-xs">old_action</td>', false);
    }

    public function test_退会済みのアカウントによる操作でも証跡として表示される(): void
    {
        $companyUser = CompanyUser::factory()->create();
        AuditLog::record('company', $companyUser->id, 'applications.view');
        $deletedId = $companyUser->id;
        $companyUser->delete();

        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee("company#{$deletedId}");
    }

    public function test_掲載企業は監査ログを閲覧できない(): void
    {
        $companyUser = CompanyUser::factory()->create();

        $this->actingAs($companyUser, 'company')
            ->get(route('admin.audit-logs.index'))
            ->assertRedirect(route('admin.login'));
    }
}
