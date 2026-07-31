<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 応募者に対する掲載企業の社内向けメモ。
 */
class ApplicationNote extends Model
{
    protected $fillable = ['application_id', 'company_user_id', 'body'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function companyUser(): BelongsTo
    {
        return $this->belongsTo(CompanyUser::class);
    }
}
