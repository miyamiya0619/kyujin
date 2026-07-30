<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SimpleXMLElement;
use ZipArchive;

/**
 * 総務省「全国地方公共団体コード」の Excel から市区町村データファイルを生成する。
 *
 * **これは開発側の保守ツールであり、顧客環境では実行しない。**
 * 生成した database/data/cities.php をリポジトリに含めて配布し、
 * 顧客環境では CitySeeder が読み込む(deploy 時の db:seed で全件入る)。
 *
 *   php artisan masters:build-city-data path/to/000925835.xlsx
 *
 * 公式ファイル: https://www.soumu.go.jp/denshijiti/code.html
 * 市区町村が合併・分割されたら新しい Excel を落として実行し直すこと。
 *
 * xlsx は ZIP 内の XML なので、追加ライブラリなしで読める。
 * この 1 箇所のためだけに phpspreadsheet を入れない。
 */
class BuildCityDataCommand extends Command
{
    protected $signature = 'masters:build-city-data
                            {path : 総務省の全国地方公共団体コード xlsx のパス}';

    protected $description = '総務省の Excel から database/data/cities.php を生成する(開発用)';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_readable($path)) {
            $this->error("ファイルが読み込めません: {$path}");

            return self::FAILURE;
        }

        $rows = $this->readSpreadsheet($path);

        if ($rows === null) {
            return self::FAILURE;
        }

        $cities = [];

        foreach ($rows as $row) {
            [$rawCode, $prefectureName, $cityName, , $cityKana] = array_pad($row, 5, '');

            // ヘッダー行を飛ばす
            if (! preg_match('/^\d{6}$/', trim($rawCode))) {
                continue;
            }

            // 市区町村名が空の行は都道府県そのもの。PrefectureSeeder が扱う。
            if (trim($cityName) === '') {
                continue;
            }

            $cities[] = [
                // 団体コードは 6 桁で末尾がチェックデジット。本システムは上位 5 桁を使う。
                'code' => substr(trim($rawCode), 0, 5),
                'name' => trim($cityName),
                // 公式データのふりがなは半角カタカナ。
                // 半角カタカナ → 全角カタカナ → ひらがな の 2 段階で変換する
                // (1 回の呼び出しでは連鎖しない)。
                'kana' => mb_convert_kana(mb_convert_kana(trim($cityKana), 'KV'), 'c'),
            ];
        }

        if ($cities === []) {
            $this->error('市区町村を 1 件も読み取れませんでした。ファイルの形式が変わっている可能性があります。');

            return self::FAILURE;
        }

        $this->write($cities);

        $this->info(sprintf('database/data/cities.php を生成しました(%d 件)。', count($cities)));

        return self::SUCCESS;
    }

    /**
     * xlsx を読んで行の配列を返す。
     *
     * @return array<int, array<int, string>>|null
     */
    private function readSpreadsheet(string $path): ?array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            $this->error('xlsx として開けませんでした。');

            return null;
        }

        // セルの文字列は共有文字列テーブルに入っていて、シート側は添字を持つ
        $shared = [];
        if (($sharedXml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            foreach ((new SimpleXMLElement($sharedXml))->si as $si) {
                // ルビなどで子要素が分かれることがあるためタグを剥がして連結する
                $shared[] = strip_tags($si->asXML());
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            $this->error('シートが見つかりませんでした。');

            return null;
        }

        $rows = [];

        foreach ((new SimpleXMLElement($sheetXml))->sheetData->row as $row) {
            $cells = [];

            foreach ($row->c as $cell) {
                $value = (string) $cell->v;
                $cells[] = ((string) $cell['t'] === 's' && isset($shared[(int) $value]))
                    ? $shared[(int) $value]
                    : $value;
            }

            $rows[] = $cells;
        }

        return $rows;
    }

    /**
     * @param  array<int, array{code: string, name: string, kana: string}>  $cities
     */
    private function write(array $cities): void
    {
        $lines = [];

        foreach ($cities as $city) {
            $lines[] = sprintf(
                "    ['%s', '%s', '%s'],",
                $city['code'],
                str_replace("'", "\\'", $city['name']),
                str_replace("'", "\\'", $city['kana']),
            );
        }

        $body = implode("\n", $lines);
        $count = count($cities);

        $contents = <<<PHP
        <?php

        /*
        |--------------------------------------------------------------------------
        | 全国の市区町村({$count} 件)
        |--------------------------------------------------------------------------
        | 総務省「全国地方公共団体コード」から生成。**手で編集しないこと。**
        | 市区町村が合併・分割されたら公式ファイルを落として再生成する。
        |
        |   php artisan masters:build-city-data path/to/000925835.xlsx
        |
        | https://www.soumu.go.jp/denshijiti/code.html
        |
        | 形式: [団体コード上位5桁, 市区町村名, ふりがな]
        | 先頭 2 桁が都道府県コードに対応する。
        */

        return [
        {$body}
        ];

        PHP;

        $path = database_path('data/cities.php');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $contents);
    }
}
