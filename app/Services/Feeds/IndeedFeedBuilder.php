<?php

namespace App\Services\Feeds;

use App\Models\JobPosting;
use App\Models\SiteSetting;

/**
 * Indeed 向け XML フィード(SPEC.md 10.1)。
 *
 * 仕様確認日: 2026-08-06。参照ドキュメント: https://docs.indeed.com/indeed-apply/xml-feed
 * (Indeed 公式のフィード仕様ページ)。
 *
 * 必須項目: title / date / referencenumber / requisitionid / url / company /
 * city / state / country / email / description。
 * 本製品は求人ごとに一意な ID のみを持ち、複数拠点での同一求人グループ化は
 * 行わないため、`requisitionid` は `referencenumber` と同じ値(求人 ID)を使う。
 * フリーテキスト項目は CDATA で包み、HTML エンティティは使わない(公式仕様どおり)。
 */
class IndeedFeedBuilder extends AbstractFeedBuilder
{
    public function mediaName(): string
    {
        return 'indeed';
    }

    public function header(): string
    {
        $site = SiteSetting::current();

        return '<?xml version="1.0" encoding="utf-8"?>'
            ."\n<source>\n"
            .'<publisher>'.$this->cdata($site->site_name)."</publisher>\n"
            .'<publisherurl>'.$this->cdata(route('public.top'))."</publisherurl>\n";
    }

    public function job(JobPosting $jobPosting): string
    {
        $workplace = $jobPosting->workplace;
        $site = SiteSetting::current();

        $xml = "<job>\n";
        $xml .= '<title>'.$this->cdata($jobPosting->title)."</title>\n";
        $xml .= '<date>'.$jobPosting->published_at?->toIso8601String()."</date>\n";
        $xml .= '<referencenumber>'.$jobPosting->id."</referencenumber>\n";
        $xml .= '<requisitionid>'.$jobPosting->id."</requisitionid>\n";
        $xml .= '<url>'.$this->cdata($this->jobUrl($jobPosting))."</url>\n";
        $xml .= '<company>'.$this->cdata($jobPosting->company->name)."</company>\n";
        $xml .= '<city>'.$this->cdata($workplace?->city?->name)."</city>\n";
        $xml .= '<state>'.$this->cdata($workplace?->prefecture?->name)."</state>\n";
        $xml .= "<country>JP</country>\n";

        if ($workplace?->postal_code) {
            $xml .= '<postalcode>'.$this->cdata($workplace->postal_code)."</postalcode>\n";
        }

        $xml .= '<email>'.$this->cdata($site->contact_email)."</email>\n";
        $xml .= '<description>'.$this->cdata($this->description($jobPosting))."</description>\n";

        if ($jobPosting->employmentType) {
            $xml .= '<jobtype>'.$this->cdata($jobPosting->employmentType->name)."</jobtype>\n";
        }

        if ($jobPosting->salary_min || $jobPosting->salary_max) {
            $xml .= '<salary>'.$this->cdata($this->salaryText($jobPosting))."</salary>\n";
        }

        if ($jobPosting->expires_at) {
            $xml .= '<expirationdate>'.$jobPosting->expires_at->toIso8601String()."</expirationdate>\n";
        }

        $xml .= "</job>\n";

        return $xml;
    }

    public function footer(): string
    {
        return "</source>\n";
    }

    private function salaryText(JobPosting $jobPosting): string
    {
        $unit = match ($jobPosting->salary_type) {
            JobPosting::SALARY_TYPE_HOURLY => '時給',
            JobPosting::SALARY_TYPE_DAILY => '日給',
            JobPosting::SALARY_TYPE_ANNUAL => '年収',
            default => '月給',
        };

        $max = $jobPosting->salary_max ?? $jobPosting->salary_min;

        return "{$unit} {$jobPosting->salary_min}円〜{$max}円";
    }
}
