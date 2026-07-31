<?php

namespace App\Services\Feeds;

use App\Models\JobPosting;

/**
 * スタンバイ向け XML フィード(SPEC.md 10.1)。
 *
 * 仕様確認日: 2026-08-06。スタンバイは XML(.xml)と テキスト(.txt)の 2 形式に対応し、
 * 文字コードは UTF-8 / EUC-JP / Shift_JIS に対応(公式マニュアル・
 * https://dfplus.io/blog/stanby-xmlfeed-update-202212 等の解説記事で確認)。
 * 本実装は UTF-8 の XML 形式のみを出力する。
 *
 * 勤務地は `<city>` に番地までの情報を入れるか、`<city>` を市区町村までにして
 * `<station>` を併記する必要がある。本製品は事業所の住所を
 * 「市区町村マスタ + 番地以降の自由入力」で持つため後者の形に合わせ、
 * `<station>` には事業所の最寄駅・アクセス情報(`workplaces.access`)を入れる。
 *
 * ⚠ この実装環境から公式マニュアル本文を直接取得できなかったため、求人検索
 * エンジン向け XML フィードの一般的な構造に基づいて組み立てている。
 * **本番投入前に必ず公式マニュアルで正式なタグ名・必須項目を再確認すること。**
 */
class StanbyFeedBuilder extends AbstractFeedBuilder
{
    public function mediaName(): string
    {
        return 'stanby';
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
        $xml .= '<station>'.$this->cdata($workplace?->access)."</station>\n";
        $xml .= '<employment_type>'.$this->cdata($jobPosting->employmentType?->name)."</employment_type>\n";

        if ($jobPosting->salary_min) {
            $xml .= '<salary_min>'.$jobPosting->salary_min."</salary_min>\n";
        }

        if ($jobPosting->salary_max) {
            $xml .= '<salary_max>'.$jobPosting->salary_max."</salary_max>\n";
        }

        $xml .= '<url>'.$this->cdata($this->jobUrl($jobPosting))."</url>\n";
        $xml .= "</job>\n";

        return $xml;
    }

    public function footer(): string
    {
        return "</jobs>\n";
    }
}
