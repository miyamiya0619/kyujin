<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 求人の審査履歴。追記のみで削除・更新はしない(証跡のため)。
 */
class JobPostingReview extends Model
{
    public const ACTION_APPROVED = 'approved';

    public const ACTION_REJECTED = 'rejected';

    /** サイト設定で審査を OFF にしている場合の自動承認。 */
    public const ACTION_AUTO_APPROVED = 'auto_approved';

    protected $fillable = ['job_posting_id', 'admin_user_id', 'action', 'comment'];

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }
}
