<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 応募時点の履歴書の写し。`payload` は一度書いたら変更しない(CLAUDE.md 3.6)。
 */
class ApplicationResumeSnapshot extends Model
{
    protected $fillable = ['application_id', 'payload', 'snapshot_at'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'snapshot_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
