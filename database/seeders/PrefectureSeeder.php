<?php

namespace Database\Seeders;

use App\Models\Prefecture;

/**
 * 都道府県マスタ(47件)。コードは全国地方公共団体コードの都道府県コード。
 * 地方区分は中学校地理で使われる 8 区分に合わせている(三重県は近畿)。
 */
class PrefectureSeeder extends MasterSeeder
{
    public function run(): void
    {
        $this->sync(Prefecture::class, [
            ['code' => '01', 'name' => '北海道',   'name_kana' => 'ほっかいどう',   'region' => '北海道'],

            ['code' => '02', 'name' => '青森県',   'name_kana' => 'あおもりけん',   'region' => '東北'],
            ['code' => '03', 'name' => '岩手県',   'name_kana' => 'いわてけん',     'region' => '東北'],
            ['code' => '04', 'name' => '宮城県',   'name_kana' => 'みやぎけん',     'region' => '東北'],
            ['code' => '05', 'name' => '秋田県',   'name_kana' => 'あきたけん',     'region' => '東北'],
            ['code' => '06', 'name' => '山形県',   'name_kana' => 'やまがたけん',   'region' => '東北'],
            ['code' => '07', 'name' => '福島県',   'name_kana' => 'ふくしまけん',   'region' => '東北'],

            ['code' => '08', 'name' => '茨城県',   'name_kana' => 'いばらきけん',   'region' => '関東'],
            ['code' => '09', 'name' => '栃木県',   'name_kana' => 'とちぎけん',     'region' => '関東'],
            ['code' => '10', 'name' => '群馬県',   'name_kana' => 'ぐんまけん',     'region' => '関東'],
            ['code' => '11', 'name' => '埼玉県',   'name_kana' => 'さいたまけん',   'region' => '関東'],
            ['code' => '12', 'name' => '千葉県',   'name_kana' => 'ちばけん',       'region' => '関東'],
            ['code' => '13', 'name' => '東京都',   'name_kana' => 'とうきょうと',   'region' => '関東'],
            ['code' => '14', 'name' => '神奈川県', 'name_kana' => 'かながわけん',   'region' => '関東'],

            ['code' => '15', 'name' => '新潟県',   'name_kana' => 'にいがたけん',   'region' => '中部'],
            ['code' => '16', 'name' => '富山県',   'name_kana' => 'とやまけん',     'region' => '中部'],
            ['code' => '17', 'name' => '石川県',   'name_kana' => 'いしかわけん',   'region' => '中部'],
            ['code' => '18', 'name' => '福井県',   'name_kana' => 'ふくいけん',     'region' => '中部'],
            ['code' => '19', 'name' => '山梨県',   'name_kana' => 'やまなしけん',   'region' => '中部'],
            ['code' => '20', 'name' => '長野県',   'name_kana' => 'ながのけん',     'region' => '中部'],
            ['code' => '21', 'name' => '岐阜県',   'name_kana' => 'ぎふけん',       'region' => '中部'],
            ['code' => '22', 'name' => '静岡県',   'name_kana' => 'しずおかけん',   'region' => '中部'],
            ['code' => '23', 'name' => '愛知県',   'name_kana' => 'あいちけん',     'region' => '中部'],

            ['code' => '24', 'name' => '三重県',   'name_kana' => 'みえけん',       'region' => '近畿'],
            ['code' => '25', 'name' => '滋賀県',   'name_kana' => 'しがけん',       'region' => '近畿'],
            ['code' => '26', 'name' => '京都府',   'name_kana' => 'きょうとふ',     'region' => '近畿'],
            ['code' => '27', 'name' => '大阪府',   'name_kana' => 'おおさかふ',     'region' => '近畿'],
            ['code' => '28', 'name' => '兵庫県',   'name_kana' => 'ひょうごけん',   'region' => '近畿'],
            ['code' => '29', 'name' => '奈良県',   'name_kana' => 'ならけん',       'region' => '近畿'],
            ['code' => '30', 'name' => '和歌山県', 'name_kana' => 'わかやまけん',   'region' => '近畿'],

            ['code' => '31', 'name' => '鳥取県',   'name_kana' => 'とっとりけん',   'region' => '中国'],
            ['code' => '32', 'name' => '島根県',   'name_kana' => 'しまねけん',     'region' => '中国'],
            ['code' => '33', 'name' => '岡山県',   'name_kana' => 'おかやまけん',   'region' => '中国'],
            ['code' => '34', 'name' => '広島県',   'name_kana' => 'ひろしまけん',   'region' => '中国'],
            ['code' => '35', 'name' => '山口県',   'name_kana' => 'やまぐちけん',   'region' => '中国'],

            ['code' => '36', 'name' => '徳島県',   'name_kana' => 'とくしまけん',   'region' => '四国'],
            ['code' => '37', 'name' => '香川県',   'name_kana' => 'かがわけん',     'region' => '四国'],
            ['code' => '38', 'name' => '愛媛県',   'name_kana' => 'えひめけん',     'region' => '四国'],
            ['code' => '39', 'name' => '高知県',   'name_kana' => 'こうちけん',     'region' => '四国'],

            ['code' => '40', 'name' => '福岡県',   'name_kana' => 'ふくおかけん',   'region' => '九州・沖縄'],
            ['code' => '41', 'name' => '佐賀県',   'name_kana' => 'さがけん',       'region' => '九州・沖縄'],
            ['code' => '42', 'name' => '長崎県',   'name_kana' => 'ながさきけん',   'region' => '九州・沖縄'],
            ['code' => '43', 'name' => '熊本県',   'name_kana' => 'くまもとけん',   'region' => '九州・沖縄'],
            ['code' => '44', 'name' => '大分県',   'name_kana' => 'おおいたけん',   'region' => '九州・沖縄'],
            ['code' => '45', 'name' => '宮崎県',   'name_kana' => 'みやざきけん',   'region' => '九州・沖縄'],
            ['code' => '46', 'name' => '鹿児島県', 'name_kana' => 'かごしまけん',   'region' => '九州・沖縄'],
            ['code' => '47', 'name' => '沖縄県',   'name_kana' => 'おきなわけん',   'region' => '九州・沖縄'],
        ]);
    }
}
