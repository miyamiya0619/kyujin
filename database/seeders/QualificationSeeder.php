<?php

namespace Database\Seeders;

use App\Models\Qualification;

/**
 * 保有資格マスタ(SPEC.md 11.1)。
 * 介護領域では資格の有無が求人検索の最重要条件になる。
 */
class QualificationSeeder extends MasterSeeder
{
    public function run(): void
    {
        $this->sync(Qualification::class, [
            // 介護
            ['code' => 'shoninsha_kenshu',      'category' => '介護',     'name' => '介護職員初任者研修'],
            ['code' => 'jitsumusha_kenshu',     'category' => '介護',     'name' => '介護福祉士実務者研修'],
            ['code' => 'kaigo_fukushishi',      'category' => '介護',     'name' => '介護福祉士'],
            ['code' => 'care_manager',          'category' => '介護',     'name' => '介護支援専門員(ケアマネジャー)'],

            // 福祉
            ['code' => 'shakai_fukushishi',     'category' => '福祉',     'name' => '社会福祉士'],
            ['code' => 'seishin_hoken_fukushi', 'category' => '福祉',     'name' => '精神保健福祉士'],
            ['code' => 'shakai_fukushi_shuji',  'category' => '福祉',     'name' => '社会福祉主事任用資格'],

            // 医療
            ['code' => 'kangoshi',              'category' => '医療',     'name' => '看護師'],
            ['code' => 'jun_kangoshi',          'category' => '医療',     'name' => '准看護師'],
            ['code' => 'hokenshi',              'category' => '医療',     'name' => '保健師'],

            // リハビリ
            ['code' => 'pt',                    'category' => 'リハビリ', 'name' => '理学療法士(PT)'],
            ['code' => 'ot',                    'category' => 'リハビリ', 'name' => '作業療法士(OT)'],
            ['code' => 'st',                    'category' => 'リハビリ', 'name' => '言語聴覚士(ST)'],
            ['code' => 'judo_seifukushi',       'category' => 'リハビリ', 'name' => '柔道整復師'],
            ['code' => 'kinou_kunren_shidou',   'category' => 'リハビリ', 'name' => '機能訓練指導員'],

            // その他
            ['code' => 'kanri_eiyoshi',         'category' => 'その他',   'name' => '管理栄養士'],
            ['code' => 'eiyoshi',               'category' => 'その他',   'name' => '栄養士'],
            ['code' => 'hoikushi',              'category' => 'その他',   'name' => '保育士'],
            ['code' => 'youchien_kyouyu',       'category' => 'その他',   'name' => '幼稚園教諭'],
            ['code' => 'chourishi',             'category' => 'その他',   'name' => '調理師'],
            ['code' => 'futsu_menkyo',          'category' => 'その他',   'name' => '普通自動車運転免許'],

            // 無資格
            ['code' => Qualification::CODE_NO_QUALIFICATION_REQUIRED, 'category' => '無資格', 'name' => '資格不問(未経験可)'],
        ]);
    }
}
