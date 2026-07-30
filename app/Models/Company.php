<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 掲載企業(メディアに求人を掲載する介護事業者)。顧客の顧客にあたる。
 */
class Company extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name', 'name_kana', 'logo_path', 'description',
        'postal_code', 'prefecture_id', 'city_id', 'address',
        'tel', 'website_url', 'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(CompanyUser::class);
    }

    public function workplaces(): HasMany
    {
        return $this->hasMany(Workplace::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class);
    }

    public function planAssignments(): HasMany
    {
        return $this->hasMany(CompanyPlanAssignment::class);
    }

    public function prefecture(): BelongsTo
    {
        return $this->belongsTo(Prefecture::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** 求人を掲載できる状態か。 */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
