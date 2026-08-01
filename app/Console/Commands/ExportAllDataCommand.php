<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\ApplicationResumeSnapshot;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Models\Workplace;
use App\Services\ImageUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * 解約時のデータ返還(SPEC.md 12.2 / TASKS.md T-17)。
 *
 * 掲載企業・事業所・求人・応募・会員のデータを CSV で書き出し、アップロード画像
 * (ロゴ・事業所写真・サイトのロゴ/キービジュアル)を合わせて 1 つの ZIP にまとめる。
 *
 * **Web からは実行できない。** 全会員の個人情報を含む一括ダウンロードのため、
 * 解約時に運営者(あなた)が SSH 経由で実行する運用にする
 * (`docs/解約時のデータ返還手順.md` を参照)。
 *
 * 件数が多くてもメモリを使い切らないよう、CSV 出力は `chunkById` で
 * 1 行ずつファイルへ書き込む(CLAUDE.md 4章)。
 */
class ExportAllDataCommand extends Command
{
    protected $signature = 'data:export';

    protected $description = '全データ(掲載企業・事業所・求人・応募・会員)と画像をZIPにエクスポートする';

    private const CHUNK_SIZE = 200;

    public function handle(): int
    {
        $timestamp = now()->format('Ymd_His');
        $workDir = storage_path("app/private/exports/{$timestamp}");

        File::makeDirectory($workDir, 0755, true);

        $this->exportCompanies($workDir);
        $this->exportWorkplaces($workDir);
        $this->exportJobPostings($workDir);
        $this->exportApplications($workDir);
        $this->exportResumeSnapshots($workDir);
        $this->exportJobSeekers($workDir);
        $this->exportCompanyUsers($workDir);
        $this->copyUploads($workDir);

        $zipPath = "{$workDir}.zip";
        $this->zip($workDir, $zipPath);
        File::deleteDirectory($workDir);

        $this->info("エクスポートが完了しました: {$zipPath}");

        return self::SUCCESS;
    }

    private function exportCompanies(string $workDir): void
    {
        $this->exportCsv($workDir, 'companies.csv', Company::query()->with(['prefecture', 'city']), [
            'id', 'name', 'name_kana', 'status', 'postal_code', 'prefecture', 'city', 'address', 'tel', 'website_url', 'created_at',
        ], fn (Company $c) => [
            $c->id, $c->name, $c->name_kana, $c->status, $c->postal_code,
            $c->prefecture?->name, $c->city?->name, $c->address, $c->tel, $c->website_url, $c->created_at,
        ]);
    }

    private function exportWorkplaces(string $workDir): void
    {
        $this->exportCsv($workDir, 'workplaces.csv', Workplace::query()->with(['company', 'prefecture', 'city', 'facilityType']), [
            'id', 'company_id', 'company_name', 'name', 'facility_type', 'postal_code', 'prefecture', 'city', 'address', 'access', 'created_at',
        ], fn (Workplace $w) => [
            $w->id, $w->company_id, $w->company->name, $w->name, $w->facilityType?->name,
            $w->postal_code, $w->prefecture?->name, $w->city?->name, $w->address, $w->access, $w->created_at,
        ]);
    }

    private function exportJobPostings(string $workDir): void
    {
        $this->exportCsv($workDir, 'job_postings.csv', JobPosting::query()->with(['company', 'workplace', 'jobCategory', 'employmentType']), [
            'id', 'company_id', 'workplace_id', 'title', 'status', 'job_category', 'employment_type',
            'salary_type', 'salary_min', 'salary_max', 'published_at', 'expires_at', 'application_count', 'created_at',
        ], fn (JobPosting $j) => [
            $j->id, $j->company_id, $j->workplace_id, $j->title, $j->status, $j->jobCategory?->name, $j->employmentType?->name,
            $j->salary_type, $j->salary_min, $j->salary_max, $j->published_at, $j->expires_at, $j->application_count, $j->created_at,
        ]);
    }

    private function exportApplications(string $workDir): void
    {
        $this->exportCsv($workDir, 'applications.csv', Application::query(), [
            'id', 'job_posting_id', 'company_id', 'job_seeker_id', 'status', 'message', 'referrer_source', 'applied_at',
        ], fn (Application $a) => [
            $a->id, $a->job_posting_id, $a->company_id, $a->job_seeker_id, $a->status, $a->message, $a->referrer_source, $a->applied_at,
        ]);
    }

    private function exportResumeSnapshots(string $workDir): void
    {
        $this->exportCsv($workDir, 'application_resume_snapshots.csv', ApplicationResumeSnapshot::query(), [
            'id', 'application_id', 'payload', 'snapshot_at',
        ], fn (ApplicationResumeSnapshot $s) => [
            $s->id, $s->application_id, json_encode($s->payload, JSON_UNESCAPED_UNICODE), $s->snapshot_at,
        ]);
    }

    private function exportJobSeekers(string $workDir): void
    {
        $this->exportCsv($workDir, 'job_seekers.csv', JobSeeker::query()->with(['prefecture', 'city']), [
            'id', 'name', 'name_kana', 'email', 'tel', 'birthday', 'prefecture', 'city', 'created_at',
        ], fn (JobSeeker $s) => [
            $s->id, $s->name, $s->name_kana, $s->email, $s->tel, $s->birthday?->toDateString(),
            $s->prefecture?->name, $s->city?->name, $s->created_at,
        ]);
    }

    private function exportCompanyUsers(string $workDir): void
    {
        $this->exportCsv($workDir, 'company_users.csv', CompanyUser::query(), [
            'id', 'company_id', 'name', 'email', 'role', 'is_active', 'created_at',
        ], fn (CompanyUser $u) => [
            $u->id, $u->company_id, $u->name, $u->email, $u->role, $u->is_active ? '1' : '0', $u->created_at,
        ]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<*>  $query
     * @param  array<int, string>  $headers
     * @param  callable(mixed): array<int, mixed>  $mapRow
     */
    private function exportCsv(string $workDir, string $filename, $query, array $headers, callable $mapRow): void
    {
        $handle = fopen("{$workDir}/{$filename}", 'w');

        // Excel(日本語環境)で文字化けしないよう UTF-8 の BOM を付ける。
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers);

        $count = 0;
        $query->chunkById(self::CHUNK_SIZE, function ($rows) use ($handle, $mapRow, &$count) {
            foreach ($rows as $row) {
                fputcsv($handle, $mapRow($row));
                $count++;
            }
        });

        fclose($handle);

        $this->info("{$filename}: {$count} 件");
    }

    /**
     * ロゴ・事業所写真・サイトのロゴ/キービジュアルを uploads ディスクから丸ごとコピーする。
     */
    private function copyUploads(string $workDir): void
    {
        $disk = Storage::disk(ImageUploadService::DISK);
        $destination = "{$workDir}/uploads";

        File::makeDirectory($destination, 0755, true);

        $count = 0;
        foreach ($disk->allFiles() as $path) {
            $target = "{$destination}/{$path}";
            File::ensureDirectoryExists(dirname($target));
            File::put($target, $disk->get($path));
            $count++;
        }

        $this->info("uploads: {$count} 件");
    }

    private function zip(string $workDir, string $zipPath): void
    {
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (File::allFiles($workDir) as $file) {
            $relativePath = ltrim(str_replace($workDir, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $zip->addFile($file->getPathname(), $relativePath);
        }

        $zip->close();
    }
}
