<?php

namespace App\Models;

use App\Models\Concerns\IsMaster;
use Illuminate\Database\Eloquent\Model;

/**
 * こだわり条件マスタ。「未経験可」「夜勤なし」「扶養内可」など。
 * 求職者が最初に触る絞り込みであり、応募数に直結する。
 */
class JobFeature extends Model
{
    use IsMaster;

    /** カテゴリ。検索画面でチェックボックスをグループ表示するために使う。 */
    public const CATEGORY_EXPERIENCE = '経験・資格';

    public const CATEGORY_SCHEDULE = '勤務時間';

    public const CATEGORY_BENEFITS = '待遇・環境';

    protected $fillable = ['code', 'category', 'name', 'sort_order', 'is_enabled'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
