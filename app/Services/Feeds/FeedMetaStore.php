<?php

namespace App\Services\Feeds;

use Illuminate\Support\Facades\Storage;

/**
 * 各媒体フィードの最終生成日時・件数を記録する。
 * 運営者管理画面で「媒体別の効果」の前提となる配信状況を見せるために使う
 * (SPEC.md 10.2 / TASKS.md T-14)。
 */
class FeedMetaStore
{
    private const DISK = 'local';

    public function record(string $media, int $jobCount): void
    {
        Storage::disk(self::DISK)->put(
            "feeds/{$media}.meta.json",
            json_encode(['generated_at' => now()->toIso8601String(), 'job_count' => $jobCount])
        );
    }

    /**
     * @return array{generated_at: string, job_count: int}|null
     */
    public function get(string $media): ?array
    {
        $path = "feeds/{$media}.meta.json";

        if (! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        return json_decode(Storage::disk(self::DISK)->get($path), true);
    }
}
