<?php

namespace App\Models;

use App\Models\Concerns\IsMaster;
use Illuminate\Database\Eloquent\Model;

/**
 * 施設形態マスタ。特養・デイサービス・訪問介護など。
 * 事業所(workplaces)に紐づき、求人検索の主要な絞り込み条件になる。
 */
class FacilityType extends Model
{
    use IsMaster;

    protected $fillable = ['code', 'category', 'name', 'short_name', 'sort_order', 'is_enabled'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * 表示用の呼び方。略称があればそれを使う(「特別養護老人ホーム」→「特養」)。
     */
    public function displayName(): string
    {
        return $this->short_name ?: $this->name;
    }
}
