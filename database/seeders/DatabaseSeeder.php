<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * 全マスタを投入する。
 *
 * **デプロイのたびに実行してよい。** 各マスタシーダーは `code` で既存行を特定するため、
 * 何度実行しても重複せず、顧客が調整した並び順・有効無効も消えない(MasterSeeder を参照)。
 *
 *     php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // このメディアの設定(1行のみ)。既にあれば何もしない。
            SiteSettingSeeder::class,

            // 市区町村は都道府県を参照するため、必ず都道府県のあとに実行する。
            PrefectureSeeder::class,
            CitySeeder::class,

            // 介護・医療・福祉の特化マスタ
            QualificationSeeder::class,
            FacilityTypeSeeder::class,
            JobCategorySeeder::class,
            EmploymentTypeSeeder::class,
            JobFeatureSeeder::class,
        ]);
    }
}
