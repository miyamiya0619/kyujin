<?php

namespace App\Models;

use App\Models\Concerns\IsMaster;
use Illuminate\Database\Eloquent\Model;

/**
 * 職種マスタ。介護職・看護師・ケアマネジャーなど。
 */
class JobCategory extends Model
{
    use IsMaster;

    protected $fillable = ['code', 'name', 'sort_order', 'is_enabled'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
