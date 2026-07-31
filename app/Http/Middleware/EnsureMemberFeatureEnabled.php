<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * サイト設定で会員機能が OFF のとき、会員登録画面を隠す(SPEC.md 5.4)。
 *
 * OFF のときは都度応募のみの運用になる想定(T-12)。403 ではなく 404 にする。
 * 「この機能は存在しない」という体で見せ、機能の有無を推測させない。
 */
class EnsureMemberFeatureEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (! SiteSetting::current()->enables_member) {
            throw new NotFoundHttpException;
        }

        return $next($request);
    }
}
