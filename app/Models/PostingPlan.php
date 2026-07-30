<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 掲載プラン。運営者が自由に定義し、掲載企業に割り当てる(SPEC.md 6.1)。
 * 各上限は null で無制限。
 */
class PostingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'max_job_postings', 'posting_duration_days', 'max_workplaces',
        'is_featured', 'monthly_price', 'is_enabled', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'max_job_postings' => 'integer',
            'posting_duration_days' => 'integer',
            'max_workplaces' => 'integer',
            'is_featured' => 'boolean',
            'monthly_price' => 'integer',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CompanyPlanAssignment::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
