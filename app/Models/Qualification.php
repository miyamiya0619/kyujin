<?php

namespace App\Models;

use App\Models\Concerns\IsMaster;
use Illuminate\Database\Eloquent\Model;

/**
 * 保有資格マスタ。
 * 介護領域では資格の有無が求人検索の最重要条件になるため、
 * 求人側(必要資格)と求職者側(保有資格)の双方から参照する。
 */
class Qualification extends Model
{
    use IsMaster;

    /** 資格不問。求人が「未経験・無資格でも可」であることを示す特別な行。 */
    public const CODE_NO_QUALIFICATION_REQUIRED = 'no_qualification_required';

    protected $fillable = ['code', 'category', 'name', 'sort_order', 'is_enabled'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
