<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\CompanyUser;
use App\Models\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * マスタ管理(運営者。TASKS.md T-16)。
 *
 * 運営者が変更できるのは `is_enabled` と `sort_order` のみ(CLAUDE.md 3.7)。
 */
class MasterManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_マスタの有効無効を切り替えられる(): void
    {
        $this->seed();

        $admin = AdminUser::factory()->create();
        $qualification = Qualification::query()->where('is_enabled', true)->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.masters.toggle', ['qualifications', $qualification]))
            ->assertRedirect(route('admin.masters.index', 'qualifications'));

        $this->assertFalse($qualification->fresh()->is_enabled);
    }

    public function test_無効化すると公開サイトの選択肢に出なくなる(): void
    {
        $this->seed();

        $admin = AdminUser::factory()->create();
        $qualification = Qualification::query()->where('is_enabled', true)->firstOrFail();

        $this->actingAs($admin, 'admin')->post(route('admin.masters.toggle', ['qualifications', $qualification]));

        $this->assertNotContains($qualification->id, Qualification::selectable()->pluck('id')->all());
    }

    public function test_並び順を変更できる(): void
    {
        $this->seed();

        $admin = AdminUser::factory()->create();
        $ids = Qualification::query()->orderBy('sort_order')->orderBy('id')->pluck('id')->all();
        $reversed = array_reverse($ids);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.masters.reorder', 'qualifications'), ['ids' => $reversed])
            ->assertRedirect(route('admin.masters.index', 'qualifications'));

        $reordered = Qualification::query()->orderBy('sort_order')->orderBy('id')->pluck('id')->all();
        $this->assertSame($reversed, $reordered);
    }

    public function test_存在しないマスタ種別は404になる(): void
    {
        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.masters.index', 'not-a-real-type'))
            ->assertNotFound();
    }

    public function test_変更が監査ログに記録される(): void
    {
        $this->seed();

        $admin = AdminUser::factory()->create();
        $qualification = Qualification::query()->firstOrFail();

        $this->actingAs($admin, 'admin')->post(route('admin.masters.toggle', ['qualifications', $qualification]));

        $this->assertDatabaseHas('audit_logs', [
            'actor_type' => 'admin',
            'actor_id' => $admin->id,
            'action' => 'masters.toggle',
            'target_id' => $qualification->id,
        ]);
    }

    public function test_掲載企業はマスタ管理画面に入れない(): void
    {
        $companyUser = CompanyUser::factory()->create();

        $this->actingAs($companyUser, 'company')
            ->get(route('admin.masters.home'))
            ->assertRedirect(route('admin.login'));
    }
}
