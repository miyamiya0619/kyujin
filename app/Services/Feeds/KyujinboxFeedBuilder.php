<?php

namespace App\Services\Feeds;

use App\Models\JobPosting;

/**
 * 求人ボックス向け XML フィード(SPEC.md 10.1)。
 *
 * 仕様確認日: 2026-08-06。求人ボックスの公式 XML フィード仕様書は
 * https://ad.kyujinbox.com/xmlfeed.pdf で公開されている(法人向けヘルプ
 * https://help-b.xn--pckua2a7gp15o89zb.com/XML%E3%83%95%E3%82%A3%E3%83%BC%E3%83%89%E9%80%A3%E6%90%BA も参照)。
 *
 * ⚠ この実装環境から PDF 本文を直接取得できなかったため、求人検索エンジン向け
 * XML フィードの一般的な構造(ルート `<jobs>` 配下に `<job>` を並べる形式。
 * 参考: https://aggregate.eole.co.jp/column/column044/)に基づいて組み立てている。
 * **本番投入前に必ず上記 PDF で正式なタグ名・必須項目を再確認すること。**
 */
class KyujinboxFeedBuilder extends AbstractFeedBuilder
{
    public function mediaName(): string
    {
        return 'kyujinbox';
    }

    public function header(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n<jobs>\n";
    }

    public function job(JobPosting $jobPosting): string
    {
        $workplace = $jobPosting->workplace;

        $xml = "<job>\n";
        $xml .= '<id>'.$jobPosting->id."</id>\n";
        $xml .= '<title>'.$this->cdata($jobPosting->title)."</title>\n";
        $xml .= '<description>'.$this->cdata($this->description($jobPosting))."</description>\n";
        $xml .= '<company>'.$this->cdata($jobPosting->company->name)."</company>\n";
        $xml .= '<prefecture>'.$this->cdata($workplace?->prefecture?->name)."</prefecture>\n";
        $xml .= '<city>'.$this->cdata($workplace?->city?->name)."</city>\n";
        $xml .= '<address>'.$this->cdata($workplace?->address)."</address>\n";
        $xml .= '<employment_type>'.$this->cdata($jobPosting->employmentType?->name)."</employment_type>\n";

        if ($jobPosting->salary_min) {
            $xml .= '<salary_min>'.$jobPosting->salary_min."</salary_min>\n";
        }

        if ($jobPosting->salary_max) {
            $xml .= '<salary_max>'.$jobPosting->salary_max."</salary_max>\n";
        }

        $xml .= '<url>'.$this->cdata($this->jobUrl($jobPosting))."</url>\n";
        $xml .= '<category>'.$this->cdata($jobPosting->jobCategory?->name)."</category>\n";
        $xml .= "</job>\n";

        return $xml;
    }

    public function footer(): string
    {
        return "</jobs>\n";
    }
}
