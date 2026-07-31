<?php

namespace App\Console\Commands;

use App\Models\JobPosting;
use App\Models\SiteSetting;
use App\Services\Feeds\FeedBuilder;
use App\Services\Feeds\FeedMetaStore;
use App\Services\Feeds\IndeedFeedBuilder;
use App\Services\Feeds\KyujinboxFeedBuilder;
use App\Services\Feeds\StanbyFeedBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * アグリゲーション媒体向け XML フィードを静的ファイルとして日次生成する(SPEC.md 10.2)。
 *
 * cron から毎日 1 回実行する想定(CloseExpiredJobPostingsCommand と同じ方針)。
 * `/feed/{media}.xml` は毎回 DB を舐めず、ここで生成した静的ファイルを配信するだけ
 * (FeedController を参照)。
 *
 * 求人が多くても実行時間・メモリの上限に達しないよう、対象求人は `chunkById` で
 * 読み進めながら 1 求人ずつファイルへ書き込む(CLAUDE.md 4章)。
 */
class GenerateAggregationFeedsCommand extends Command
{
    protected $signature = 'feeds:generate';

    protected $description = 'アグリゲーション媒体向けの XML フィードを生成する';

    private const DISK = 'local';

    private const CHUNK_SIZE = 200;

    public function handle(FeedMetaStore $meta): int
    {
        $builders = [
            new IndeedFeedBuilder,
            new KyujinboxFeedBuilder,
            new StanbyFeedBuilder,
        ];

        // 運営者の設定が優先(SPEC.md 10.2)。OFF なら配信対象を空にし、
        // 既存のフィードファイルも空の一覧で上書きする(配信停止を即座に反映するため)。
        $feedEnabled = SiteSetting::current()->enables_external_feed;

        Storage::disk(self::DISK)->makeDirectory('feeds');

        foreach ($builders as $builder) {
            $jobCount = $this->generate($builder, $feedEnabled);
            $meta->record($builder->mediaName(), $jobCount);
            $this->info("{$builder->mediaName()}: {$jobCount} 件を出力しました。");
        }

        return self::SUCCESS;
    }

    private function generate(FeedBuilder $builder, bool $feedEnabled): int
    {
        $media = $builder->mediaName();
        $disk = Storage::disk(self::DISK);
        $tempRelative = "feeds/.{$media}.xml.tmp";
        $finalRelative = "feeds/{$media}.xml";

        $handle = fopen($disk->path($tempRelative), 'w');
        fwrite($handle, $builder->header());

        $jobCount = 0;

        if ($feedEnabled) {
            JobPosting::query()->feedEligible()->chunkById(self::CHUNK_SIZE, function ($jobPostings) use ($handle, $builder, &$jobCount) {
                foreach ($jobPostings as $jobPosting) {
                    fwrite($handle, $builder->job($jobPosting));
                    $jobCount++;
                }
            });
        }

        fwrite($handle, $builder->footer());
        fclose($handle);

        $disk->move($tempRelative, $finalRelative);

        return $jobCount;
    }
}
