<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobPosting;
use Illuminate\Http\Response;

/**
 * サイトマップ XML(SEO)。
 *
 * 公開中の求人・稼働中の企業ページのみを含める(CLAUDE.md 3.5)。
 * 件数が増えても軽く保てるよう、必要な列だけ取得しチャンク処理する。
 *
 * ⚠ Blade ではなくこのクラスで直接 XML 文字列を組み立てる。
 *   `<?xml version="1.0"?>` の宣言を Blade テンプレートの先頭に書くと、
 *   Blade コンパイラが `<?` を PHP の開始タグとして誤認識し、
 *   コンパイル後の PHP が構文エラーになる(実際に発生した)。
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => route('public.jobs.index'), 'priority' => '0.9'],
        ];

        JobPosting::published()
            ->select('id', 'updated_at')
            ->orderBy('id')
            ->chunk(500, function ($jobPostings) use (&$urls) {
                foreach ($jobPostings as $jobPosting) {
                    $urls[] = [
                        'loc' => route('public.jobs.show', $jobPosting),
                        'lastmod' => $jobPosting->updated_at->toAtomString(),
                        'priority' => '0.8',
                    ];
                }
            });

        Company::query()
            ->where('status', Company::STATUS_ACTIVE)
            ->select('id', 'updated_at')
            ->orderBy('id')
            ->chunk(500, function ($companies) use (&$urls) {
                foreach ($companies as $company) {
                    $urls[] = [
                        'loc' => route('public.companies.show', $company),
                        'lastmod' => $company->updated_at->toAtomString(),
                        'priority' => '0.6',
                    ];
                }
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url['loc'])."</loc>\n";
            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>'.e($url['lastmod'])."</lastmod>\n";
            }
            $xml .= '    <priority>'.$url['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'text/xml; charset=UTF-8');
    }
}
