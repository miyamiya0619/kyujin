<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 応募。選考ステータスの遷移は T-13(SPEC.md 8.3)で本格的に扱う。
 */
class Application extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    /**
     * 選考ステータスの表示名(SPEC.md 8.3)。他のステータスは T-13 で追加する。
     *
     * @var array<string, string>
     */
    public const STATUS_LABELS = [
        self::STATUS_NEW => '新規応募',
    ];

    protected $fillable = [
        'job_posting_id', 'job_seeker_id', 'company_id',
        'status', 'message', 'referrer_source', 'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
        ];
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(JobSeeker::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function resumeSnapshot(): HasOne
    {
        return $this->hasOne(ApplicationResumeSnapshot::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
