<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CompanyPlanAssignment extends Model
{
    protected $fillable = ['company_id', 'posting_plan_id', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function postingPlan(): BelongsTo
    {
        return $this->belongsTo(PostingPlan::class);
    }

    /** 今日時点で有効な割当だけに絞る。 */
    public function scopeActive(Builder $query, ?Carbon $date = null): Builder
    {
        $date ??= now()->startOfDay();

        return $query
            ->where('starts_at', '<=', $date)
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $date));
    }
}
