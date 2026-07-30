<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Workplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 運営者による事業所の代行編集。
 * 立ち上げ期に掲載企業へ代わって登録する運用を想定する(SPEC.md 11.6)。
 */
class WorkplaceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_運営者は任意の企業に事業所を代行登録できる(): void
    {
        $admin = AdminUser::factory()->create();
        $company = Company::factory()->create();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.companies.workplaces.store', $company), ['name' => '代行登録の事業所'])
            ->assertRedirect(route('admin.companies.workplaces.index', $company));

        $this->assertDatabaseHas('workplaces', [
            'company_id' => $company->id,
            'name' => '代行登録の事業所',
        ]);
    }

    public function test_他社の事業所を_ur_lで組み替えても編集できない(): void
    {
        $admin = AdminUser::factory()->create();
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $workplaceB = Workplace::factory()->for($companyB)->create();

        // 企業 A の URL に企業 B の事業所 ID を混ぜる
        $this->actingAs($admin, 'admin')
            ->get(route('admin.companies.workplaces.edit', [$companyA, $workplaceB]))
            ->assertNotFound();
    }

    public function test_一覧はその企業の事業所だけを表示する(): void
    {
        $admin = AdminUser::factory()->create();
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        Workplace::factory()->for($companyA)->create(['name' => 'A社の事業所']);
        Workplace::factory()->for($companyB)->create(['name' => 'B社の事業所']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.companies.workplaces.index', $companyA))
            ->assertOk()
            ->assertSee('A社の事業所')
            ->assertDontSee('B社の事業所');
    }

    public function test_掲載企業は運営管理画面の代行登録ルートに入れない(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->get(route('admin.companies.workplaces.index', $company))
            ->assertRedirect(route('admin.login'));
    }
}
