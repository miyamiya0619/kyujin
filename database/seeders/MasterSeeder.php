<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * マスタシーダーの基底クラス。
 *
 * マスタは製品が配布するデータだが、`is_enabled` と `sort_order` は顧客が管理する。
 * バージョンアップ時に全環境でシーダーを再実行するため、
 * **顧客が調整した並び順と有効/無効を絶対に上書きしてはいけない。**
 *
 * - 製品が管理する項目(name / category など) … 毎回最新に更新する
 * - 顧客が管理する項目(is_enabled / sort_order) … 新規作成時のみ設定する
 */
abstract class MasterSeeder extends Seeder
{
    /**
     * マスタ行を投入する。`code` で既存行を特定するため何度実行してもよい。
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<int, array<string, mixed>>  $rows  各行は 'code' を必ず含む
     */
    protected function sync(string $modelClass, array $rows): void
    {
        $order = 0;

        foreach ($rows as $row) {
            // 10 刻みにしておくと、顧客が「この 2 つの間に入れたい」と並べ替えるときに
            // 既存の値を触らずに済む。
            $order += 10;

            /** @var Model $model */
            $model = $modelClass::firstOrNew(['code' => $row['code']]);

            foreach ($row as $column => $value) {
                if ($column === 'code') {
                    continue;
                }
                $model->{$column} = $value;
            }

            // 顧客の調整を消さないため、新規作成時だけ初期値を入れる。
            if (! $model->exists) {
                $model->sort_order = $order;
                $model->is_enabled = true;
            }

            $model->save();
        }
    }
}
