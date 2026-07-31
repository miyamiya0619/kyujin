<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * 応募の流入元(SPEC.md 10.2)。
 *
 * 外部媒体(Indeed 等)のフィードから求人詳細に着地した際に付く URL パラメータ
 * `?ref=indeed` をセッションに保持し、その後 会員登録 → 応募 とページを跨いでも
 * 流入元が引き継がれるようにする。
 */
class ReferrerSourceResolver
{
    private const SESSION_KEY = 'referrer_source';

    public const DEFAULT_SOURCE = 'direct';

    /**
     * @var array<int, string>
     */
    private const VALID_SOURCES = ['indeed', 'kyujinbox', 'stanby', 'google'];

    public function capture(Request $request): void
    {
        $ref = $request->query('ref');

        if (is_string($ref) && in_array($ref, self::VALID_SOURCES, true)) {
            $request->session()->put(self::SESSION_KEY, $ref);
        }
    }

    public function resolve(Request $request): string
    {
        return $request->session()->get(self::SESSION_KEY, self::DEFAULT_SOURCE);
    }
}
