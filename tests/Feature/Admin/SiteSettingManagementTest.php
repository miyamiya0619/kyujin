<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\CompanyUser;
use App\Models\SiteSetting;
use App\Services\ImageUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * サイト設定(運営者。TASKS.md T-16)。
 */
class SiteSettingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_サイト設定を変更でき即座に反映される(): void
    {
        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.site-settings.update'), [
                'site_name' => '介護求人メディアテスト',
                'theme' => 'default',
                'theme_color' => '#123456',
                'requires_review' => '1',
                'enables_member' => '1',
                'enables_posting_plan' => '1',
                'enables_external_feed' => '1',
            ])
            ->assertRedirect(route('admin.site-settings.edit'));

        $this->assertSame('介護求人メディアテスト', SiteSetting::current()->site_name);
        $this->assertSame('#123456', SiteSetting::current()->theme_color);
    }

    public function test_必須項目が無いと保存できない(): void
    {
        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.site-settings.update'), ['site_name' => ''])
            ->assertSessionHasErrors('site_name');
    }

    public function test_ロゴをアップロードできる(): void
    {
        Storage::fake(ImageUploadService::DISK);

        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')->put(route('admin.site-settings.update'), [
            'site_name' => 'テストメディア',
            'theme' => 'default',
            'theme_color' => '#2563eb',
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ]);

        $this->assertNotNull(SiteSetting::current()->logo_path);
        Storage::disk(ImageUploadService::DISK)->assertExists(SiteSetting::current()->logo_path);
    }

    public function test_変更が監査ログに記録される(): void
    {
        $admin = AdminUser::factory()->create();

        $this->actingAs($admin, 'admin')->put(route('admin.site-settings.update'), [
            'site_name' => 'テストメディア',
            'theme' => 'default',
            'theme_color' => '#2563eb',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_type' => 'admin',
            'actor_id' => $admin->id,
            'action' => 'site_settings.update',
        ]);
    }

    public function test_掲載企業はサイト設定を変更できない(): void
    {
        $companyUser = CompanyUser::factory()->create();

        $this->actingAs($companyUser, 'company')
            ->get(route('admin.site-settings.edit'))
            ->assertRedirect(route('admin.login'));
    }
}
