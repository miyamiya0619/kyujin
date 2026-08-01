<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationResumeSnapshot;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\JobSeeker;
use App\Services\ImageUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/**
 * 解約時のデータ返還(全データエクスポート。TASKS.md T-17)。
 */
class ExportAllDataCommandTest extends TestCase
{
    use RefreshDatabase;

    private ?string $exportDir = null;

    protected function tearDown(): void
    {
        if ($this->exportDir && File::isDirectory(dirname($this->exportDir))) {
            File::deleteDirectory(dirname($this->exportDir));
        }

        parent::tearDown();
    }

    public function test_全データと画像がzipに出力される(): void
    {
        Storage::fake(ImageUploadService::DISK);
        Storage::disk(ImageUploadService::DISK)->put('companies/logos/sample.webp', 'dummy-image-content');

        $company = Company::factory()->create(['name' => 'エクスポート対象企業']);
        $jobPosting = JobPosting::factory()->for($company)->published()->create(['title' => 'エクスポート対象求人']);
        $jobSeeker = JobSeeker::factory()->create(['name' => 'エクスポート太郎']);
        $application = Application::factory()->create([
            'job_posting_id' => $jobPosting->id,
            'job_seeker_id' => $jobSeeker->id,
            'company_id' => $company->id,
        ]);
        ApplicationResumeSnapshot::create([
            'application_id' => $application->id,
            'payload' => ['name' => 'スナップショット太郎'],
            'snapshot_at' => now(),
        ]);

        $this->artisan('data:export')->assertSuccessful();

        $zipPath = $this->findGeneratedZip();
        $this->assertNotNull($zipPath, 'ZIP ファイルが生成されていること');

        $zip = new ZipArchive;
        $zip->open($zipPath);

        $this->assertStringContainsString('エクスポート対象企業', $zip->getFromName('companies.csv'));
        $this->assertStringContainsString('エクスポート対象求人', $zip->getFromName('job_postings.csv'));
        $this->assertStringContainsString('エクスポート太郎', $zip->getFromName('job_seekers.csv'));
        $this->assertStringContainsString((string) $application->id, $zip->getFromName('applications.csv'));
        $this->assertStringContainsString('スナップショット太郎', $zip->getFromName('application_resume_snapshots.csv'));
        $this->assertNotFalse($zip->locateName('uploads/companies/logos/sample.webp'), 'アップロード画像が含まれること');

        $zip->close();
    }

    private function findGeneratedZip(): ?string
    {
        $exportsDir = storage_path('app/private/exports');
        $files = File::glob("{$exportsDir}/*.zip");

        if (empty($files)) {
            return null;
        }

        $latest = end($files);
        $this->exportDir = $latest;

        return $latest;
    }
}
