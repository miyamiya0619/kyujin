<?php

namespace Database\Seeders;

use App\Models\EmploymentType;

/**
 * 雇用形態マスタ(SPEC.md 11.4)。
 */
class EmploymentTypeSeeder extends MasterSeeder
{
    public function run(): void
    {
        $this->sync(EmploymentType::class, [
            ['code' => 'seishain',        'name' => '正社員'],
            ['code' => 'keiyaku',         'name' => '契約社員'],
            ['code' => 'part',            'name' => 'パート・アルバイト'],
            ['code' => 'haken',           'name' => '派遣社員'],
            ['code' => 'gyomu_itaku',     'name' => '業務委託'],
            ['code' => 'shokai_yotei',    'name' => '紹介予定派遣'],
        ]);
    }
}
