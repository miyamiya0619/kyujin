<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Models\SiteSetting;
use App\Services\ImageUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * 解約時の完全削除(SPEC.md 12.2 / TASKS.md T-17)。
 *
 * **取り消せない。実行前に必ず `data:export` でデータを退避すること。**
 *
 * Web からは実行できない。全会員の個人情報を含む環境全体の破棄のため、
 * 解約時に運営者(あなた)が SSH 経由で実行する運用にする
 * (`docs/解約時のデータ返還手順.md` を参照)。
 *
 * 二段階確認: (1) メディア名の入力一致 (2) 明示的な yes 確認。
 * 実行前に `audit_logs` へ記録してから削除する。`audit_logs` テーブル自体は
 * 削除対象から除外しているため、全データを消した後もこの証跡だけは読める。
 *
 * 資格・施設形態・職種などの製品マスタ(顧客の個人情報を含まない配布データ)は
 * 削除対象に含めない。削除しても顧客のデータ保護には寄与せず、環境が
 * 使用不能になるだけのため。
 */
class DestroyAllDataCommand extends Command
{
    protected $signature = 'data:destroy-all {--admin= : 実行者を特定する運営者アカウントのメールアドレス(必須)}';

    protected $description = '解約時に全データ(製品マスタと監査ログを除く)を完全に削除する。取り消せない';

    /**
     * @var array<int, string>
     */
    private const EXCLUDED_TABLES = [
        'migrations', 'audit_logs',
        'prefectures', 'cities', 'qualifications', 'facility_types',
        'job_categories', 'employment_types', 'job_features',
    ];

    public function handle(): int
    {
        $adminEmail = $this->option('admin');

        if (! $adminEmail) {
            $this->error('--admin=<運営者のメールアドレス> を指定してください(監査ログに実行者を記録するため必須です)。');

            return self::FAILURE;
        }

        $admin = AdminUser::where('email', $adminEmail)->first();

        if (! $admin) {
            $this->error("運営者アカウントが見つかりません: {$adminEmail}");

            return self::FAILURE;
        }

        $siteName = SiteSetting::current()->site_name;

        $this->warn('この操作はメディアの全データ(掲載企業・求人・応募・会員等)を完全に削除します。取り消せません。');
        $this->warn('実行前に data:export でデータを退避したことを確認してください。');

        $this->table(['対象', '件数'], [
            ['掲載企業', Company::count()],
            ['求人', JobPosting::count()],
            ['応募', Application::count()],
            ['求職者(会員)', JobSeeker::count()],
            ['アップロード画像', count(Storage::disk(ImageUploadService::DISK)->allFiles())],
        ]);

        $typed = $this->ask("確認のため、メディア名「{$siteName}」を正確に入力してください");

        if ($typed !== $siteName) {
            $this->error('メディア名が一致しないため中止しました。');

            return self::FAILURE;
        }

        if (! $this->confirm('本当に全データを完全に削除しますか? この操作は取り消せません。')) {
            $this->info('中止しました。');

            return self::FAILURE;
        }

        // 削除の前に記録する。audit_logs は削除対象から除外しているため、
        // 全データを消した後もこの証跡だけは読める。
        AuditLog::record('admin', $admin->id, 'data.destroy_all');

        $this->truncateTables();
        $this->deleteUploads();

        $this->info('全データを削除しました。');

        return self::SUCCESS;
    }

    private function truncateTables(): void
    {
        $databaseName = DB::connection()->getDatabaseName();

        $tables = DB::select(
            'SELECT table_name AS name FROM information_schema.tables WHERE table_schema = ?',
            [$databaseName]
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            if (in_array($table->name, self::EXCLUDED_TABLES, true)) {
                continue;
            }

            DB::table($table->name)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        SiteSetting::forgetMemo();
    }

    private function deleteUploads(): void
    {
        $disk = Storage::disk(ImageUploadService::DISK);

        foreach ($disk->directories() as $directory) {
            $disk->deleteDirectory($directory);
        }

        foreach ($disk->files() as $file) {
            $disk->delete($file);
        }
    }
}
