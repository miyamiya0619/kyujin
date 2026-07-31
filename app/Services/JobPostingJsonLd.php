<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\SiteSetting;

/**
 * Google しごと検索向けの JobPosting 構造化データ(JSON-LD)。
 *
 * 必須・推奨項目は Google のドキュメントに準拠する。
 * https://developers.google.com/search/docs/appearance/structured-data/job-posting
 *
 * 注記: app/Http/Controllers/Feed 配下(T-14 で作成予定)でも同じ求人データを扱うが、
 * あちらは Indeed 等の XML フィード形式でありここの JSON-LD とは別物。
 */
class JobPostingJsonLd
{
    /**
     * @return array<string, mixed>
     */
    public function build(JobPosting $jobPosting): array
    {
        $workplace = $jobPosting->workplace;
        $siteName = SiteSetting::current()->site_name;

        $data = [
            '@context' => 'https://schema.org/',
            '@type' => 'JobPosting',
            'title' => $jobPosting->title,
            'description' => $this->description($jobPosting),
            'datePosted' => $jobPosting->published_at?->toDateString(),
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $jobPosting->company->name,
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'JP',
                    'addressRegion' => $workplace?->prefecture?->name,
                    'addressLocality' => $workplace?->city?->name,
                    'streetAddress' => $workplace?->address,
                ],
            ],
            // 掲載元メディア名。hiringOrganization と求職者が混同しないよう明記する。
            'identifier' => [
                '@type' => 'PropertyValue',
                'name' => $siteName,
                'value' => (string) $jobPosting->id,
            ],
        ];

        if ($jobPosting->expires_at) {
            $data['validThrough'] = $jobPosting->expires_at->toIso8601String();
        }

        if ($jobPosting->employmentType) {
            $data['employmentType'] = $this->mapEmploymentType($jobPosting->employmentType->code);
        }

        if ($jobPosting->salary_min || $jobPosting->salary_max) {
            $data['baseSalary'] = [
                '@type' => 'MonetaryAmount',
                'currency' => 'JPY',
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $jobPosting->salary_min,
                    'maxValue' => $jobPosting->salary_max ?? $jobPosting->salary_min,
                    'unitText' => $this->mapSalaryUnit($jobPosting->salary_type),
                ],
            ];
        }

        return $data;
    }

    /**
     * description は必須項目でありプレーンテキストでは弱いため、
     * 主要項目を HTML として組み立てる(Google は HTML タグを許容する)。
     */
    private function description(JobPosting $jobPosting): string
    {
        $parts = array_filter([
            $jobPosting->description,
            $jobPosting->working_hours ? "【勤務時間】{$jobPosting->working_hours}" : null,
            $jobPosting->holidays ? "【休日休暇】{$jobPosting->holidays}" : null,
            $jobPosting->benefits ? "【待遇】{$jobPosting->benefits}" : null,
        ]);

        return e(implode("\n\n", $parts));
    }

    /**
     * schema.org の employmentType は列挙値が決まっている。
     * マスタの code をそこに写像する。
     */
    private function mapEmploymentType(string $code): string
    {
        return match ($code) {
            'seishain' => 'FULL_TIME',
            'keiyaku' => 'CONTRACTOR',
            'part' => 'PART_TIME',
            'haken' => 'TEMPORARY',
            'gyomu_itaku' => 'CONTRACTOR',
            'shokai_yotei' => 'TEMPORARY',
            default => 'OTHER',
        };
    }

    private function mapSalaryUnit(?string $salaryType): string
    {
        return match ($salaryType) {
            JobPosting::SALARY_TYPE_HOURLY => 'HOUR',
            JobPosting::SALARY_TYPE_DAILY => 'DAY',
            JobPosting::SALARY_TYPE_MONTHLY => 'MONTH',
            JobPosting::SALARY_TYPE_ANNUAL => 'YEAR',
            default => 'MONTH',
        };
    }
}
