<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\Feeds\FeedMetaStore;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

/**
 * 媒体別のアグリゲーション効果(SPEC.md 10.2)。
 *
 * 「掲載しただけで応募が来る」ことを顧客が実感できるかどうかが継続の鍵であり、
 * その根拠として媒体別の配信件数・応募件数を見せる画面。
 */
class FeedController extends Controller
{
    public const MEDIA_NAMES = [
        'direct' => '自社サイト',
        'indeed' => 'Indeed',
        'kyujinbox' => '求人ボックス',
        'stanby' => 'スタンバイ',
        'google' => 'Google しごと検索',
    ];

    public function index(FeedMetaStore $meta): View
    {
        $applicationCounts = Application::query()
            ->select('referrer_source', DB::raw('count(*) as count'))
            ->groupBy('referrer_source')
            ->pluck('count', 'referrer_source');

        $feeds = collect(['indeed', 'kyujinbox', 'stanby'])->mapWithKeys(fn ($media) => [
            $media => $meta->get($media),
        ]);

        return view('admin.feeds.index', [
            'mediaNames' => self::MEDIA_NAMES,
            'applicationCounts' => $applicationCounts,
            'feeds' => $feeds,
        ]);
    }
}
