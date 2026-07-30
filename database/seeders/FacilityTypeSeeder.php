<?php

namespace Database\Seeders;

use App\Models\FacilityType;

/**
 * 施設形態マスタ(SPEC.md 11.2)。
 * 略称(特養・老健など)は現場で日常的に使われるため、検索と表示の双方で使う。
 */
class FacilityTypeSeeder extends MasterSeeder
{
    public function run(): void
    {
        $this->sync(FacilityType::class, [
            // 入所系
            ['code' => 'tokuyo',            'category' => '入所系',    'name' => '特別養護老人ホーム',           'short_name' => '特養'],
            ['code' => 'roken',             'category' => '入所系',    'name' => '介護老人保健施設',             'short_name' => '老健'],
            ['code' => 'kaigo_iryoin',      'category' => '入所系',    'name' => '介護医療院',                   'short_name' => null],
            ['code' => 'yuryo_rojin_home',  'category' => '入所系',    'name' => '有料老人ホーム',               'short_name' => null],
            ['code' => 'sakoju',            'category' => '入所系',    'name' => 'サービス付き高齢者向け住宅',   'short_name' => 'サ高住'],
            ['code' => 'group_home',        'category' => '入所系',    'name' => 'グループホーム',               'short_name' => null],
            ['code' => 'care_house',        'category' => '入所系',    'name' => 'ケアハウス',                   'short_name' => null],

            // 通所系
            ['code' => 'day_service',       'category' => '通所系',    'name' => 'デイサービス',                 'short_name' => null],
            ['code' => 'day_care',          'category' => '通所系',    'name' => 'デイケア',                     'short_name' => null],
            ['code' => 'chiiki_tsusho',     'category' => '通所系',    'name' => '地域密着型通所介護',           'short_name' => null],

            // 訪問系
            ['code' => 'homon_kaigo',       'category' => '訪問系',    'name' => '訪問介護',                     'short_name' => null],
            ['code' => 'homon_kango',       'category' => '訪問系',    'name' => '訪問看護',                     'short_name' => null],
            ['code' => 'homon_nyuyoku',     'category' => '訪問系',    'name' => '訪問入浴',                     'short_name' => null],
            ['code' => 'homon_rehab',       'category' => '訪問系',    'name' => '訪問リハビリ',                 'short_name' => null],

            // 複合
            ['code' => 'shokibo_takinou',   'category' => '複合',      'name' => '小規模多機能型居宅介護',       'short_name' => '小多機'],
            ['code' => 'kango_takinou',     'category' => '複合',      'name' => '看護小規模多機能型居宅介護',   'short_name' => '看多機'],
            ['code' => 'short_stay',        'category' => '複合',      'name' => 'ショートステイ',               'short_name' => null],

            // 相談・支援
            ['code' => 'kyotaku_shien',     'category' => '相談・支援', 'name' => '居宅介護支援事業所',           'short_name' => null],
            ['code' => 'chiiki_hokatsu',    'category' => '相談・支援', 'name' => '地域包括支援センター',         'short_name' => null],

            // 障害福祉
            ['code' => 'shogaisha_shien',   'category' => '障害福祉',  'name' => '障害者支援施設',               'short_name' => null],
            ['code' => 'shuro_keizoku',     'category' => '障害福祉',  'name' => '就労継続支援A型・B型',         'short_name' => null],
            ['code' => 'seikatsu_kaigo',    'category' => '障害福祉',  'name' => '生活介護',                     'short_name' => null],
            ['code' => 'houkago_day',       'category' => '障害福祉',  'name' => '放課後等デイサービス',         'short_name' => '放デイ'],

            // 医療・保育
            ['code' => 'byoin',             'category' => '医療・保育', 'name' => '病院',                         'short_name' => null],
            ['code' => 'clinic',            'category' => '医療・保育', 'name' => 'クリニック',                   'short_name' => null],
            ['code' => 'hoikuen',           'category' => '医療・保育', 'name' => '保育園',                       'short_name' => null],
            ['code' => 'nintei_kodomoen',   'category' => '医療・保育', 'name' => '認定こども園',                 'short_name' => null],
        ]);
    }
}
