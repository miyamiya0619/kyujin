<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 選考ステータスの変更履歴。追記のみで削除・更新はしない(証跡のため)。
 */
class ApplicationStatusLog extends Model
{
    protected $fillable = ['application_id', 'company_user_id', 'from_status', 'to_status'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function companyUser(): BelongsTo
    {
        return $this->belongsTo(CompanyUser::class);
    }
}
