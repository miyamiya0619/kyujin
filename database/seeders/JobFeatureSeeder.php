<?php

namespace Database\Seeders;

use App\Models\JobFeature;

/**
 * こだわり条件マスタ(SPEC.md 11.5)。
 *
 * 求職者が最初に触る絞り込みであり、応募数に直結する。
 * カテゴリは検索画面でチェックボックスをグループ表示するために持つ。
 */
class JobFeatureSeeder extends MasterSeeder
{
    public function run(): void
    {
        $this->sync(JobFeature::class, [
            // 経験・資格
            ['code' => 'mikeiken_ok',      'category' => JobFeature::CATEGORY_EXPERIENCE, 'name' => '未経験可'],
            ['code' => 'mushikaku_ok',     'category' => JobFeature::CATEGORY_EXPERIENCE, 'name' => '無資格可'],
            ['code' => 'blank_ok',         'category' => JobFeature::CATEGORY_EXPERIENCE, 'name' => 'ブランクOK'],
            ['code' => 'shikaku_shien',    'category' => JobFeature::CATEGORY_EXPERIENCE, 'name' => '資格取得支援あり'],
            ['code' => 'kenshu_jujitsu',   'category' => JobFeature::CATEGORY_EXPERIENCE, 'name' => '研修充実'],
            ['code' => 'rokudai_katsuyaku', 'category' => JobFeature::CATEGORY_EXPERIENCE, 'name' => '60代活躍中'],

            // 勤務時間
            ['code' => 'yakin_nashi',      'category' => JobFeature::CATEGORY_SCHEDULE,   'name' => '夜勤なし'],
            ['code' => 'yakin_senju',      'category' => JobFeature::CATEGORY_SCHEDULE,   'name' => '夜勤専従'],
            ['code' => 'nikkin_nomi',      'category' => JobFeature::CATEGORY_SCHEDULE,   'name' => '日勤のみ'],
            ['code' => 'shu1_ok',          'category' => JobFeature::CATEGORY_SCHEDULE,   'name' => '週1日〜OK'],
            ['code' => 'doniti_yasumi',    'category' => JobFeature::CATEGORY_SCHEDULE,   'name' => '土日休み'],
            ['code' => 'fuyonai_ok',       'category' => JobFeature::CATEGORY_SCHEDULE,   'name' => '扶養内可'],
            ['code' => 'fukugyo_ok',       'category' => JobFeature::CATEGORY_SCHEDULE,   'name' => '副業OK'],

            // 待遇・環境
            ['code' => 'kuruma_tsukin',    'category' => JobFeature::CATEGORY_BENEFITS,   'name' => '車通勤可'],
            ['code' => 'chushajo',         'category' => JobFeature::CATEGORY_BENEFITS,   'name' => '駐車場あり'],
            ['code' => 'kotsuhi_shikyu',   'category' => JobFeature::CATEGORY_BENEFITS,   'name' => '交通費支給'],
            ['code' => 'shoyo_ari',        'category' => JobFeature::CATEGORY_BENEFITS,   'name' => '賞与あり'],
            ['code' => 'taishokukin',      'category' => JobFeature::CATEGORY_BENEFITS,   'name' => '退職金あり'],
            ['code' => 'shakai_hoken',     'category' => JobFeature::CATEGORY_BENEFITS,   'name' => '社会保険完備'],
            ['code' => 'takujisho',        'category' => JobFeature::CATEGORY_BENEFITS,   'name' => '託児所あり'],
        ]);
    }
}
