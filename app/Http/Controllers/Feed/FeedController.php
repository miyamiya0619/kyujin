<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * アグリゲーション媒体向け XML フィードの配信(SPEC.md 10.1)。
 *
 * `GenerateAggregationFeedsCommand` が日次生成した静的ファイルを返すだけで、
 * リクエストのたびに DB を舐めない(SPEC.md 10.2)。
 */
class FeedController extends Controller
{
    private const DISK = 'local';

    public function indeed(): Response
    {
        return $this->serve('indeed');
    }

    public function kyujinbox(): Response
    {
        return $this->serve('kyujinbox');
    }

    public function stanby(): Response
    {
        return $this->serve('stanby');
    }

    private function serve(string $media): Response
    {
        $disk = Storage::disk(self::DISK);
        $path = "feeds/{$media}.xml";

        if (! $disk->exists($path)) {
            // まだ一度も生成されていない(cron 未実行・導入直後)。
            throw new NotFoundHttpException;
        }

        return response($disk->get($path), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
