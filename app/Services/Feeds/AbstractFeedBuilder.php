<?php

namespace App\Services\Feeds;

use App\Models\JobPosting;

/**
 * 各媒体ビルダー共通のヘルパー。
 */
abstract class AbstractFeedBuilder implements FeedBuilder
{
    public function contentType(): string
    {
        return 'application/xml; charset=UTF-8';
    }

    /**
     * 応募は必ず自社メディアに着地させ、URL パラメータで流入元を判別する
     * (SPEC.md 10.2)。`ReferrerSourceResolver` が対応する値のリストと一致させること。
     */
    protected function jobUrl(JobPosting $jobPosting): string
    {
        return route('public.jobs.show', $jobPosting).'?ref='.$this->mediaName();
    }

    /**
     * XML の特殊文字を CDATA で包む。改行や HTML タグを含む可能性のある
     * フリーテキスト項目(タイトル・本文・企業名など)はこれを通すこと。
     */
    protected function cdata(?string $value): string
    {
        return '<![CDATA['.str_replace(']]>', ']]&gt;', (string) $value).']]>';
    }

    /**
     * 仕事内容の本文。JobPostingJsonLd と同じ組み立て方(主要項目を連結)にする。
     */
    protected function description(JobPosting $jobPosting): string
    {
        $parts = array_filter([
            $jobPosting->description,
            $jobPosting->working_hours ? "【勤務時間】{$jobPosting->working_hours}" : null,
            $jobPosting->holidays ? "【休日休暇】{$jobPosting->holidays}" : null,
            $jobPosting->benefits ? "【待遇】{$jobPosting->benefits}" : null,
        ]);

        return implode("\n\n", $parts);
    }
}
