<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Application;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Models\Qualification;
use App\Models\SiteSetting;
use App\Services\ImageUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 解約時の完全削除(TASKS.md T-17)。
 *
 * ⚠ このコマンドは内部で TRUNCATE を使う。MySQL の TRUNCATE は暗黙のコミットを
 * 伴うため、`RefreshDatabase` が各テストにかけているトランザクションはロール
 * バックできなくなり、削除が実際に確定してしまう。確認の途中で中止する
 * テスト(TRUNCATE まで到達しないもの)は通常どおりロールバックされるが、
 * 削除まで完了させるテストは実行後に明示的に `migrate:fresh` でスキーマを
 * 作り直し、後続の他のテストファイルに影響を残さないようにしている。
 */
class DestroyAllDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_二段階確認を経て全データが削除され監査ログに残る(): void
    {
        Storage::fake(ImageUploadService::DISK);
        Storage::disk(ImageUploadService::DISK)->put('companies/logos/sample.webp', 'dummy');

        SiteSetting::current()->update(['site_name' => '削除テストメディア']);
        $admin = AdminUser::factory()->create(['email' => 'admin@example.com']);
        $company = Company::factory()->create();
        CompanyUser::factory()->for($company)->create();
        $jobPosting = JobPosting::factory()->for($company)->published()->create();
        $jobSeeker = JobSeeker::factory()->create();
        Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'job_seeker_id' => $jobSeeker->id,
            'company_id' => $company->id,
        ]);

        $this->artisan('data:destroy-all', ['--admin' => 'admin@example.com'])
            ->expectsQuestion('確認のため、メディア名「削除テストメディア」を正確に入力してください', '削除テストメディア')
            ->expectsConfirmation('本当に全データを完全に削除しますか? この操作は取り消せません。', 'yes')
            ->assertSuccessful();

        $this->assertDatabaseCount('companies', 0);
        $this->assertDatabaseCount('company_users', 0);
        $this->assertDatabaseCount('job_postings', 0);
        $this->assertDatabaseCount('applications', 0);
        $this->assertDatabaseCount('job_seekers', 0);
        $this->assertDatabaseCount('admin_users', 0);

        $this->assertDatabaseHas('audit_logs', [
            'actor_type' => 'admin',
            'actor_id' => $admin->id,
            'action' => 'data.destroy_all',
        ]);

        Storage::disk(ImageUploadService::DISK)->assertMissing('companies/logos/sample.webp');

        $this->restoreSchema();
    }

    public function test_製品マスタは削除対象に含まれない(): void
    {
        $this->seed();

        AdminUser::factory()->create(['email' => 'admin@example.com']);
        $qualificationCountBefore = Qualification::count();
        $this->assertGreaterThan(0, $qualificationCountBefore);

        SiteSetting::current()->update(['site_name' => '削除テストメディア']);

        $this->artisan('data:destroy-all', ['--admin' => 'admin@example.com'])
            ->expectsQuestion('確認のため、メディア名「削除テストメディア」を正確に入力してください', '削除テストメディア')
            ->expectsConfirmation('本当に全データを完全に削除しますか? この操作は取り消せません。', 'yes')
            ->assertSuccessful();

        $this->assertSame($qualificationCountBefore, Qualification::count(), '製品マスタは削除されない');

        $this->restoreSchema();
    }

    public function test_メディア名の入力が一致しないと中止される(): void
    {
        SiteSetting::current()->update(['site_name' => '削除テストメディア']);
        AdminUser::factory()->create(['email' => 'admin@example.com']);
        $company = Company::factory()->create();

        $this->artisan('data:destroy-all', ['--admin' => 'admin@example.com'])
            ->expectsQuestion('確認のため、メディア名「削除テストメディア」を正確に入力してください', '違う名前')
            ->assertFailed();

        $this->assertModelExists($company);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'data.destroy_all']);
    }

    public function test_最終確認でnoと答えると中止される(): void
    {
        SiteSetting::current()->update(['site_name' => '削除テストメディア']);
        AdminUser::factory()->create(['email' => 'admin@example.com']);
        $company = Company::factory()->create();

        $this->artisan('data:destroy-all', ['--admin' => 'admin@example.com'])
            ->expectsQuestion('確認のため、メディア名「削除テストメディア」を正確に入力してください', '削除テストメディア')
            ->expectsConfirmation('本当に全データを完全に削除しますか? この操作は取り消せません。', 'no')
            ->assertFailed();

        $this->assertModelExists($company);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'data.destroy_all']);
    }

    public function test_運営者アカウントを指定しないと実行できない(): void
    {
        $company = Company::factory()->create();

        $this->artisan('data:destroy-all')->assertFailed();

        $this->assertModelExists($company);
    }

    public function test_存在しない運営者メールでは実行できない(): void
    {
        $company = Company::factory()->create();

        $this->artisan('data:destroy-all', ['--admin' => 'not-exist@example.com'])->assertFailed();

        $this->assertModelExists($company);
    }

    /**
     * TRUNCATE で確定してしまったスキーマ状態を作り直す。
     * `RefreshDatabase` は初回だけマイグレーションを実行し、以降は
     * 「既にマイグレーション済み」という前提でトランザクションだけを使う。
     * この前提が壊れないよう、実際に全削除まで到達したテストの直後に
     * 明示的にスキーマを復元しておく。
     */
    private function restoreSchema(): void
    {
        $this->artisan('migrate:fresh')->run();
    }
}
