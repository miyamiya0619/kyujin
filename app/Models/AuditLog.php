<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * 監査ログ。追記のみで削除・更新はしない。
 *
 * 記録対象(SPEC.md 5.3): 審査の承認/差戻し、応募者情報の閲覧・CSV出力、
 * 掲載プランの変更、サイト設定の変更、データ削除。
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_type', 'actor_id', 'action', 'target_type', 'target_id', 'ip_address',
    ];

    /**
     * 監査ログを 1 件記録する。
     *
     * @param  'admin'|'company'|'seeker'  $actorType
     */
    public static function record(
        string $actorType,
        int $actorId,
        string $action,
        ?EloquentModel $target = null,
        ?string $ipAddress = null,
    ): self {
        return self::create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $action,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'ip_address' => $ipAddress,
        ]);
    }
}
