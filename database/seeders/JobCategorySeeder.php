<?php

namespace Database\Seeders;

use App\Models\JobCategory;

/**
 * 職種マスタ(SPEC.md 11.3)。
 */
class JobCategorySeeder extends MasterSeeder
{
    public function run(): void
    {
        $this->sync(JobCategory::class, [
            ['code' => 'kaigo_helper',        'name' => '介護職・ヘルパー'],
            ['code' => 'nurse',               'name' => '看護師・准看護師'],
            ['code' => 'care_manager',        'name' => 'ケアマネジャー'],
            ['code' => 'seikatsu_soudanin',   'name' => '生活相談員'],
            ['code' => 'service_teikyo',      'name' => 'サービス提供責任者'],
            ['code' => 'kinou_kunren',        'name' => '機能訓練指導員'],
            ['code' => 'kanrishoku',          'name' => '管理職・施設長'],
            ['code' => 'soutai_driver',       'name' => '送迎ドライバー'],
            ['code' => 'chouri',              'name' => '調理師・調理補助'],
            ['code' => 'jimu',                'name' => '事務・受付'],
            ['code' => 'hoikushi',            'name' => '保育士'],
            ['code' => 'jido_shidouin',       'name' => '児童指導員'],
        ]);
    }
}
