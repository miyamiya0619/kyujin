<?php

namespace App\Services\Feeds;

use App\Models\JobPosting;

/**
 * アグリゲーション媒体向け XML フィードの組み立て。媒体ごとに実装を分ける
 * (SPEC.md 10.3: フォーマットは媒体ごとに異なり、変更される)。
 *
 * `GenerateAggregationFeedsCommand` がチャンク処理で 1 求人ずつ `job()` を呼び、
 * ファイルへ逐次書き込む。巨大な XML 文字列を一度にメモリへ載せないための設計
 * (共有レンタルサーバーの実行時間・メモリ上限。CLAUDE.md 4章)。
 */
interface FeedBuilder
{
    public function mediaName(): string;

    public function contentType(): string;

    public function header(): string;

    public function job(JobPosting $jobPosting): string;

    public function footer(): string;
}
