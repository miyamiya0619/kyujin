<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\City;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Prefecture;
use App\Notifications\CompanyUserInvitationNotification;
use App\Services\ImageUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 運営者による掲載企業の管理。
 */
class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminUser::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    public function test_掲載企業を登録できる(): void
    {
        $this->post(route('admin.companies.store'), [
            'name' => 'さくら介護サービス',
            'name_kana' => 'さくらかいごさーびす',
            'tel' => '03-1234-5678',
            'status' => Company::STATUS_ACTIVE,
        ])->assertRedirect();

        $this->assertDatabaseHas('companies', [
            'name' => 'さくら介護サービス',
            'status' => Company::STATUS_ACTIVE,
        ]);
    }

    public function test_企業名が無ければ登録できない(): void
    {
        $this->post(route('admin.companies.store'), [
            'name' => '',
            'status' => Company::STATUS_ACTIVE,
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseCount('companies', 0);
    }

    public function test_市区町村が都道府県と一致しないと登録できない(): void
    {
        $this->seed();

        $tokyo = Prefecture::where('code', '13')->firstOrFail();
        $osakaCity = City::where('code', '27100')->firstOrFail();

        $this->post(route('admin.companies.store'), [
            'name' => 'テスト介護',
            'status' => Company::STATUS_ACTIVE,
            'prefecture_id' => $tokyo->id,
            'city_id' => $osakaCity->id,
        ])->assertSessionHasErrors('city_id');
    }

    public function test_ロゴをアップロードすると_web_pに変換される(): void
    {
        Storage::fake(ImageUploadService::DISK);

        $this->post(route('admin.companies.store'), [
            'name' => 'ロゴありの会社',
            'status' => Company::STATUS_ACTIVE,
            'logo' => UploadedFile::fake()->image('logo.png', 400, 400),
        ])->assertRedirect();

        $company = Company::firstOrFail();

        $this->assertNotNull($company->logo_path);
        $this->assertStringEndsWith('.webp', $company->logo_path);
        Storage::disk(ImageUploadService::DISK)->assertExists($company->logo_path);
    }

    public function test_ロゴを差し替えると古いファイルが消える(): void
    {
        Storage::fake(ImageUploadService::DISK);

        $company = Company::factory()->create();

        $this->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'status' => Company::STATUS_ACTIVE,
            'logo' => UploadedFile::fake()->image('first.png'),
        ]);

        $firstPath = $company->fresh()->logo_path;

        $this->put(route('admin.companies.update', $company), [
            'name' => $company->name,
            'status' => Company::STATUS_ACTIVE,
            'logo' => UploadedFile::fake()->image('second.png'),
        ]);

        $secondPath = $company->fresh()->logo_path;

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk(ImageUploadService::DISK)->assertMissing($firstPath);
        Storage::disk(ImageUploadService::DISK)->assertExists($secondPath);
    }

    public function test_担当者を追加すると招待メールが送られる(): void
    {
        Notification::fake();

        $company = Company::factory()->create();

        $this->post(route('admin.companies.users.store', $company), [
            'name' => '企業花子',
            'email' => 'hanako@example.com',
            'role' => CompanyUser::ROLE_OWNER,
        ])->assertRedirect();

        $user = CompanyUser::where('email', 'hanako@example.com')->firstOrFail();

        $this->assertSame($company->id, $user->company_id);
        $this->assertTrue($user->isOwner());

        Notification::assertSentTo($user, CompanyUserInvitationNotification::class);
    }

    public function test_招待時にパスワードは受け取らない(): void
    {
        Notification::fake();

        $company = Company::factory()->create();

        // 画面にパスワード欄は無いが、リクエストを組み立てられても無視されること
        $this->post(route('admin.companies.users.store', $company), [
            'name' => '企業花子',
            'email' => 'hanako@example.com',
            'role' => CompanyUser::ROLE_MEMBER,
            'password' => 'attacker-chosen-password',
        ]);

        $user = CompanyUser::where('email', 'hanako@example.com')->firstOrFail();

        $this->assertFalse(
            Hash::check('attacker-chosen-password', $user->password),
            'リクエストで渡されたパスワードが設定されてはいけない'
        );
    }

    public function test_同じメールアドレスの担当者は登録できない(): void
    {
        $existing = CompanyUser::factory()->create(['email' => 'dup@example.com']);
        $other = Company::factory()->create();

        $this->post(route('admin.companies.users.store', $other), [
            'name' => '別の人',
            'email' => 'dup@example.com',
            'role' => CompanyUser::ROLE_MEMBER,
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, CompanyUser::where('email', 'dup@example.com')->count());
        $this->assertSame($existing->company_id, CompanyUser::where('email', 'dup@example.com')->first()->company_id);
    }

    public function test_他社の担当者を_ur_lで指定しても操作できない(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userB = CompanyUser::factory()->for($companyB)->create();

        // 企業Aの URL に企業Bの担当者 ID を混ぜる
        $this->post(route('admin.companies.users.toggle', [$companyA, $userB]))
            ->assertNotFound();

        $this->assertTrue($userB->fresh()->is_active, '他社の担当者の状態が変わってはいけない');
    }

    public function test_担当者の有効無効を切り替えられる(): void
    {
        $company = Company::factory()->create();
        $user = CompanyUser::factory()->for($company)->create();

        $this->post(route('admin.companies.users.toggle', [$company, $user]))->assertRedirect();

        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_掲載企業の削除ルートは提供しない(): void
    {
        // 求人と応募が紐づくため物理削除はしない。ステータスを archived にして止める。
        $company = Company::factory()->create();

        $this->delete(route('admin.companies.show', $company))->assertStatus(405);
    }
}
