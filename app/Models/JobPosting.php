<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 求人。審査フロー(T-08)を経て公開される。
 *
 * ⚠ status = published 以外を公開サイト・検索結果・フィード・サイトマップに
 *   出してはいけない(CLAUDE.md 3.5)。一覧・検索は必ず scopePublished() を通すこと。
 */
class JobPosting extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_CLOSED = 'closed';

    public const SALARY_TYPE_MONTHLY = 'monthly';

    public const SALARY_TYPE_HOURLY = 'hourly';

    public const SALARY_TYPE_DAILY = 'daily';

    public const SALARY_TYPE_ANNUAL = 'annual';

    protected $fillable = [
        'company_id', 'workplace_id', 'title',
        'job_category_id', 'employment_type_id',
        'salary_type', 'salary_min', 'salary_max',
        'working_hours', 'holidays', 'benefits', 'description',
        'has_night_shift', 'status', 'published_at', 'expires_at',
        'is_featured', 'allow_external_feed',
    ];

    protected function casts(): array
    {
        return [
            'has_night_shift' => 'boolean',
            'is_featured' => 'boolean',
            'allow_external_feed' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'view_count' => 'integer',
            'application_count' => 'integer',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    public function jobCategory(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class);
    }

    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class);
    }

    public function qualifications(): BelongsToMany
    {
        return $this->belongsToMany(Qualification::class, 'job_posting_qualification');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(JobFeature::class, 'job_posting_feature');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(JobPostingReview::class)->latest('id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * 公開サイト・検索・フィードに出してよい求人だけに絞る。
     * 一覧・検索の起点は必ずこれを通すこと(CLAUDE.md 3.5)。
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /** 掲載期限が過ぎているか。過ぎていれば公開中でも表示してはいけない。 */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
