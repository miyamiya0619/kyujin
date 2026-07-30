<?php

namespace Tests\Feature\Company;

use App\Models\City;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\FacilityType;
use App\Models\Prefecture;
use App\Models\Workplace;
use App\Services\ImageUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 掲載企業による事業所の管理。
 *
 * **URL に企業 ID を含めない設計**(CLAUDE.md 3.8)が実際に機能していることを確認する。
 * 対象は常にログイン中の担当者の所属企業。
 */
class WorkplaceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_自社の事業所を登録できる(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->post(route('company.workplaces.store'), ['name' => '桜台デイサービス'])
            ->assertRedirect(route('company.workplaces.index'));

        $this->assertDatabaseHas('workplaces', [
            'company_id' => $company->id,
            'name' => '桜台デイサービス',
        ]);
    }

    public function test_リクエストに他社のcompany_idを混ぜても他社の事業所にはならない(): void
    {
        $mine = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($mine)->create();

        $this->actingAs($user, 'company')->post(route('company.workplaces.store'), [
            'name' => '不正な指定',
            'company_id' => $other->id,
        ]);

        $workplace = Workplace::where('name', '不正な指定')->firstOrFail();

        $this->assertSame($mine->id, $workplace->company_id);
    }

    public function test_一覧には自社の事業所だけが表示される(): void
    {
        $mine = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($mine)->create();

        Workplace::factory()->for($mine)->create(['name' => '自社の事業所']);
        Workplace::factory()->for($other)->create(['name' => '他社の事業所']);

        $this->actingAs($user, 'company')
            ->get(route('company.workplaces.index'))
            ->assertOk()
            ->assertSee('自社の事業所')
            ->assertDontSee('他社の事業所');
    }

    public function test_他社の事業所を編集できない(): void
    {
        $mine = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($mine)->create();
        $otherWorkplace = Workplace::factory()->for($other)->create();

        $this->actingAs($user, 'company')
            ->get(route('company.workplaces.edit', $otherWorkplace))
            ->assertNotFound();
    }

    public function test_他社の事業所を更新できない(): void
    {
        $mine = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($mine)->create();
        $otherWorkplace = Workplace::factory()->for($other)->create(['name' => '元の名前']);

        $this->actingAs($user, 'company')
            ->put(route('company.workplaces.update', $otherWorkplace), ['name' => '書き換え'])
            ->assertNotFound();

        $this->assertSame('元の名前', $otherWorkplace->fresh()->name);
    }

    public function test_他社の事業所を削除できない(): void
    {
        $mine = Company::factory()->create();
        $other = Company::factory()->create();
        $user = CompanyUser::factory()->for($mine)->create();
        $otherWorkplace = Workplace::factory()->for($other)->create();

        $this->actingAs($user, 'company')
            ->delete(route('company.workplaces.destroy', $otherWorkplace))
            ->assertNotFound();

        $this->assertModelExists($otherWorkplace);
    }

    public function test_自社の事業所を削除できる(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();

        $this->actingAs($user, 'company')
            ->delete(route('company.workplaces.destroy', $workplace))
            ->assertRedirect(route('company.workplaces.index'));

        $this->assertModelMissing($workplace);
    }

    public function test_写真をアップロードすると_web_pに変換される(): void
    {
        Storage::fake(ImageUploadService::DISK);

        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $this->actingAs($user, 'company')->post(route('company.workplaces.store'), [
            'name' => '写真ありの事業所',
            'photo' => UploadedFile::fake()->image('photo.png', 800, 600),
        ]);

        $workplace = Workplace::where('name', '写真ありの事業所')->firstOrFail();

        $this->assertStringEndsWith('.webp', $workplace->photo_path);
        Storage::disk(ImageUploadService::DISK)->assertExists($workplace->photo_path);
    }

    public function test_無効化されたマスタは選択肢に出ない(): void
    {
        $this->seed();

        // 「デイサービス」は他の施設形態名にも部分一致するため、
        // 一意な名称を持つ「特別養護老人ホーム」で検証する。
        $facilityType = FacilityType::where('code', 'tokuyo')->firstOrFail();
        $facilityType->update(['is_enabled' => false]);

        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $response = $this->actingAs($user, 'company')->get(route('company.workplaces.create'));

        $response->assertOk()->assertDontSee('特別養護老人ホーム', false);
    }

    public function test_市区町村が都道府県と一致しないと保存できない(): void
    {
        $this->seed();

        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $tokyo = Prefecture::where('code', '13')->firstOrFail();
        $osakaCity = City::where('code', '27100')->firstOrFail();

        $this->actingAs($user, 'company')->post(route('company.workplaces.store'), [
            'name' => '不整合な事業所',
            'prefecture_id' => $tokyo->id,
            'city_id' => $osakaCity->id,
        ])->assertSessionHasErrors('city_id');

        $this->assertDatabaseMissing('workplaces', ['name' => '不整合な事業所']);
    }

    /**
     * job_postings テーブルは T-07 で作る。それまでは常に false を返し、
     * 削除を妨げないことを確認する(Workplace::hasJobPostings を参照)。
     */
    public function test_job_postingsテーブルが無い間は求人なし扱いで削除できる(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();
        $workplace = Workplace::factory()->for($company)->create();

        $this->assertFalse($workplace->hasJobPostings());

        $this->actingAs($user, 'company')
            ->delete(route('company.workplaces.destroy', $workplace))
            ->assertRedirect(route('company.workplaces.index'));

        $this->assertModelMissing($workplace);
    }
}
