<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Prefecture;

/**
 * 市区町村マスタ(全国 1,747 件)。
 *
 * データは総務省「全国地方公共団体コード」から生成した database/data/cities.php を読む。
 * リポジトリに同梱しているため、**顧客環境では deploy 時の db:seed だけで全件入る。**
 * 手作業での取り込みは不要。
 *
 * 市区町村が合併・分割されたら、公式ファイルを落として再生成してから配布する。
 *
 *   php artisan masters:build-city-data path/to/000925835.xlsx
 *
 * なお 政令指定都市の行政区(札幌市中央区など)は公式データに含まれないため、
 * このマスタにも入らない。東京 23 区は特別区として含まれる。
 */
class CitySeeder extends MasterSeeder
{
    public function run(): void
    {
        $prefectureIds = Prefecture::pluck('id', 'code');

        if ($prefectureIds->isEmpty()) {
            throw new \RuntimeException('都道府県マスタが空です。PrefectureSeeder を先に実行してください。');
        }

        $rows = [];

        foreach (require database_path('data/cities.php') as [$code, $name, $kana]) {
            $prefectureCode = substr($code, 0, 2);

            $rows[] = [
                'code' => $code,
                'prefecture_id' => $prefectureIds[$prefectureCode],
                'name' => $name,
                'name_kana' => $kana,
            ];
        }

        $this->sync(City::class, $rows);
    }
}
