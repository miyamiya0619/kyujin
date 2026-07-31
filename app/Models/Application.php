<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 応募。選考ステータスの遷移は SPEC.md 8.3。
 */
class Application extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_DOCUMENT_SCREENING = 'document_screening';

    public const STATUS_INTERVIEW_ARRANGING = 'interview_arranging';

    public const STATUS_INTERVIEWED = 'interviewed';

    public const STATUS_OFFER = 'offer';

    public const STATUS_HIRED = 'hired';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_DECLINED = 'declined';

    /**
     * 選考ステータスの表示名(SPEC.md 8.3)。
     *
     * @var array<string, string>
     */
    public const STATUS_LABELS = [
        self::STATUS_NEW => '新規応募',
        self::STATUS_DOCUMENT_SCREENING => '書類選考中',
        self::STATUS_INTERVIEW_ARRANGING => '面接調整中',
        self::STATUS_INTERVIEWED => '面接済',
        self::STATUS_OFFER => '内定',
        self::STATUS_HIRED => '入社',
        self::STATUS_REJECTED => '不採用',
        self::STATUS_DECLINED => '辞退',
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

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ApplicationStatusLog::class)->latest('id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class)->latest('id');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
