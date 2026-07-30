<?php

namespace Tests\Feature\Company;

use App\Models\AdminUser;
use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 掲載企業による自社情報の編集。
 *
 * **他社の情報に触れないこと**がこの製品の信用に直結する。
 * URL に企業 ID を含めない設計にして、構造的に他社を指定できないようにしている。
 */
class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_自社の企業情報を編集できる(): void
    {
        $company = Company::factory()->create(['name' => '編集前']);
        $user = CompanyUser::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->put(route('company.profile.update'), ['name' => '編集後'])
            ->assertRedirect(route('company.profile.edit'));

        $this->assertSame('編集後', $company->fresh()->name);
    }

    public function test_編集画面には自社の情報だけが表示される(): void
    {
        $mine = Company::factory()->create(['name' => 'わたしの介護サービス']);
        $other = Company::factory()->create(['name' => 'よその介護サービス']);
        $user = CompanyUser::factory()->for($mine)->create();

        $this->actingAs($user, 'company')
            ->get(route('company.profile.edit'))
            ->assertOk()
            ->assertSee('わたしの介護サービス')
            ->assertDontSee('よその介護サービス');
    }

    /**
     * 対象の企業は常にログイン中の担当者から決まる。
     * リクエストに company_id を混ぜても他社は書き換わらない。
     */
    public function test_リクエストに他社の_i_dを混ぜても他社は書き換わらない(): void
    {
        $mine = Company::factory()->create(['name' => 'わたしの介護サービス']);
        $other = Company::factory()->create(['name' => 'よその介護サービス']);
        $user = CompanyUser::factory()->for($mine)->create();

        $this->actingAs($user, 'company')
            ->put(route('company.profile.update'), [
                'name' => '書き換え',
                'company_id' => $other->id,
                'id' => $other->id,
            ]);

        $this->assertSame('書き換え', $mine->fresh()->name, '自社は更新される');
        $this->assertSame('よその介護サービス', $other->fresh()->name, '他社は変わらない');
    }

    /**
     * 掲載停止・契約終了は運営者だけが決める。
     * 掲載企業が自分で掲載を再開できてはいけない。
     */
    public function test_掲載企業はステータスを変更できない(): void
    {
        $company = Company::factory()->suspended()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->put(route('company.profile.update'), [
                'name' => $company->name,
                'status' => Company::STATUS_ACTIVE,
            ]);

        $this->assertSame(
            Company::STATUS_SUSPENDED,
            $company->fresh()->status,
            '掲載企業がステータスを変えられてはいけない'
        );
    }

    public function test_企業名が無ければ更新できない(): void
    {
        $company = Company::factory()->create(['name' => '編集前']);
        $user = CompanyUser::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->put(route('company.profile.update'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertSame('編集前', $company->fresh()->name);
    }

    public function test_運営者は掲載企業の画面に入れない(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin')
            ->get(route('company.profile.edit'))
            ->assertRedirect(route('company.login'));
    }
}
