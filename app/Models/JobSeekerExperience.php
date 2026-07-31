<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 求職者の職務経歴。1 件が 1 つの勤務先に対応する。
 */
class JobSeekerExperience extends Model
{
    protected $fillable = [
        'job_seeker_id', 'organization_name', 'job_title',
        'started_on', 'ended_on', 'description', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(JobSeeker::class);
    }

    public function isCurrent(): bool
    {
        return $this->ended_on === null;
    }
}
