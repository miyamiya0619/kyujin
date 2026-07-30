<?php

namespace App\Models;

use App\Models\Concerns\IsMaster;
use Illuminate\Database\Eloquent\Model;

/**
 * 雇用形態マスタ。正社員・パート・派遣など。
 */
class EmploymentType extends Model
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
