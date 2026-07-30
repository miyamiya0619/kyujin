<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Prefecture;
use Illuminate\Console\Command;

/**
 * 総務省の「全国地方公共団体コード」CSV から市区町村マスタを全件取り込む。
 *
 * CitySeeder が投入するのは主要 74 件だけで、全国約 1,900 の市区町村を網羅していない。
 * 介護事業所は全国の町村にも存在するため、**本番導入前にこのコマンドで全件を取り込むこと。**
 *
 * CSV の想定形式(総務省の公開形式):
 *   団体コード, 都道府県名(漢字), 市区町村名(漢字), 都道府県名(カナ), 市区町村名(カナ)
 *   011002, 北海道, 札幌市, ホッカイドウ, サッポロシ
 *
 * 団体コードは 6 桁(末尾はチェックデジット)。本システムは上位 5 桁を code として使う。
 */
class ImportCitiesCommand extends Command
{
    protected $signature = 'masters:import-cities
                            {path : 総務省の全国地方公共団体コード CSV のパス}
                            {--encoding=SJIS-win : CSV の文字コード}
                            {--dry-run : 取り込まずに件数だけ表示する}';

    protected $description = '総務省の全国地方公共団体コード CSV から市区町村マスタを取り込む';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_readable($path)) {
            $this->error("CSV が読み込めません: {$path}");

            return self::FAILURE;
        }

        $prefectureIds = Prefecture::pluck('id', 'code');

        if ($prefectureIds->isEmpty()) {
            $this->error('都道府県マスタが空です。先に php artisan db:seed を実行してください。');

            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map(
                fn ($value) => mb_convert_encoding((string) $value, 'UTF-8', $this->option('encoding')),
                $row
            );

            [$rawCode, $prefectureName, $cityName] = array_pad(array_slice($row, 0, 3), 3, '');
            $cityKana = $row[4] ?? '';

            // ヘッダー行と、市区町村名が空の行(都道府県そのものの行)は飛ばす
            if (! preg_match('/^\d{6}$/', trim($rawCode)) || trim($cityName) === '') {
                $skipped++;

                continue;
            }

            $code = substr(trim($rawCode), 0, 5);
            $prefectureCode = substr($code, 0, 2);

            if (! isset($prefectureIds[$prefectureCode])) {
                $skipped++;

                continue;
            }

            $city = City::firstOrNew(['code' => $code]);

            // 製品が管理する項目は毎回更新する
            $city->prefecture_id = $prefectureIds[$prefectureCode];
            $city->name = trim($cityName);
            $city->name_kana = mb_convert_kana(trim($cityKana), 'c');

            // 顧客が管理する項目は新規作成時のみ設定する(CitySeeder と同じ方針)
            if (! $city->exists) {
                $city->sort_order = (int) substr($code, 2);
                $city->is_enabled = true;
                $created++;
            } else {
                $updated++;
            }

            if (! $this->option('dry-run')) {
                $city->save();
            }
        }

        fclose($handle);

        $this->info(sprintf(
            '%s 新規 %d 件 / 更新 %d 件 / スキップ %d 件',
            $this->option('dry-run') ? '[dry-run]' : '取り込み完了:',
            $created,
            $updated,
            $skipped
        ));

        return self::SUCCESS;
    }
}
