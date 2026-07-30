<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * マスタテーブル共通の振る舞い。
 *
 * マスタは製品が配布するデータだが、`is_enabled` と `sort_order` は顧客が管理する。
 * バージョンアップでシーダーを再実行してもこの 2 つを上書きしないこと(MasterSeeder を参照)。
 *
 * 入力画面・検索画面の選択肢を組み立てるときは必ず enabled() を通すこと。
 * 無効化したマスタが選択肢に残ると、顧客が「消したはずのものが出る」と感じる。
 */
trait IsMaster
{
    /**
     * 顧客が有効にしているものだけに絞る。
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * 顧客が指定した並び順。同順のものは id 順で安定させる。
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * 選択肢として使えるものを並び順で取得する。
     * 入力フォームと検索フォームはこれを使う。
     */
    public function scopeSelectable(Builder $query): Builder
    {
        return $query->enabled()->ordered();
    }
}
