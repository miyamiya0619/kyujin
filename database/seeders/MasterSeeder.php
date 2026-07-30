<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * マスタシーダーの基底クラス。
 *
 * マスタは製品が配布するデータだが、`is_enabled` と `sort_order` は顧客が管理する。
 * バージョンアップ時に全環境でシーダーを再実行するため、
 * **顧客が調整した並び順と有効/無効を絶対に上書きしてはいけない。**
 *
 * - 製品が管理する項目(name / category など) … 差分があれば更新する
 * - 顧客が管理する項目(is_enabled / sort_order) … 新規作成時のみ設定する
 *
 * ## 1 行ずつ処理しない理由
 *
 * 市区町村は 1,747 件ある。1 行ずつ firstOrNew + save すると 3,500 クエリになり、
 * 本番(エックスサーバー共有レンタル)の実行時間上限に達して deploy が途中で止まる。
 * 既存行を 1 回で読み、新規はまとめて INSERT、更新は差分があるものだけに絞る。
 */
abstract class MasterSeeder extends Seeder
{
    /** 1 回の INSERT にまとめる件数。プレースホルダ上限に触れない程度に抑える。 */
    private const CHUNK = 200;

    /**
     * マスタ行を投入する。`code` で既存行を特定するため何度実行してもよい。
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<int, array<string, mixed>>  $rows  各行は 'code' を必ず含む
     */
    protected function sync(string $modelClass, array $rows): void
    {
        $table = (new $modelClass)->getTable();
        $now = now();

        $existing = DB::table($table)->get()->keyBy('code');

        $insert = [];
        $order = 0;

        foreach ($rows as $row) {
            // 10 刻みにしておくと、顧客が「この 2 つの間に入れたい」と並べ替えるときに
            // 既存の値を触らずに済む。
            $order += 10;

            $code = $row['code'];

            if (! isset($existing[$code])) {
                $insert[] = [
                    ...$row,
                    // 顧客が管理する項目。新規作成時だけ初期値を入れる。
                    'sort_order' => $order,
                    'is_enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                continue;
            }

            $current = (array) $existing[$code];
            $changed = [];

            foreach ($row as $column => $value) {
                if ($column === 'code') {
                    continue;
                }

                // 型がゆるく異なる(DB の "1" と PHP の 1)ため緩い比較にする
                if (($current[$column] ?? null) != $value) {
                    $changed[$column] = $value;
                }
            }

            if ($changed !== []) {
                DB::table($table)->where('code', $code)->update([...$changed, 'updated_at' => $now]);
            }
        }

        foreach (array_chunk($insert, self::CHUNK) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }
}
