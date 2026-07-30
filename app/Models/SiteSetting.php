<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * このメディアの設定。**必ず 1 行だけ存在する。**
 *
 * 取得は必ず {@see self::current()} を使うこと。
 *
 * ## キャッシュを使わない理由
 *
 * 本番のキャッシュストアは database(エックスサーバーに Redis が無いため)。
 * 1 行のテーブルをその DB キャッシュに載せても、DB クエリが DB クエリに変わるだけで
 * 速くならない。さらに Eloquent モデルをキャッシュに入れるとシリアライズを経由するため、
 * 復元に失敗して __PHP_Incomplete_Class になる事故が起きる(実際に起きた)。
 *
 * リクエスト内のメモ化だけで十分に速く、キューワーカーや別プロセスからも
 * 常に最新の設定が読めるという利点もある。
 */
class SiteSetting extends Model
{
    /** リクエスト内のメモ化。1 リクエストにつき 1 クエリで済ませる。 */
    private static ?self $memo = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'requires_review' => 'boolean',
            'enables_member' => 'boolean',
            'enables_posting_plan' => 'boolean',
            'max_job_postings' => 'integer',
            'max_companies' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // 設定を変えたら同一リクエスト内でも即座に反映されるようにする
        static::saved(fn () => static::forgetMemo());
        static::deleted(fn () => static::forgetMemo());
    }

    /**
     * このメディアの設定を取得する。
     *
     * 行が無ければマイグレーションの既定値で作る。
     * シーダーより先に Web リクエストが来ても落ちないようにするため。
     *
     * ⚠ `create([])` の直後は DB 側のデフォルト値(`requires_review` など)が
     *   モデルインスタンスに反映されず、空のまま渡した属性が `null` になる
     *   (Eloquent は INSERT 時に渡していない属性を DB から読み直さない)。
     *   実際にこれが原因で `requires_review` が `null` = falsy 扱いになり、
     *   審査 OFF の分岐に誤って入る不具合が起きた。`refresh()` で DB の実値を読み直す。
     */
    public static function current(): self
    {
        return static::$memo ??= static::query()->first()
            ?? static::query()->create([])->refresh();
    }

    /**
     * テストや、設定を書き換えた直後に呼ぶ。
     */
    public static function forgetMemo(): void
    {
        static::$memo = null;
    }

    /**
     * パッケージ販売プランの上限(SPEC.md 6.2)。あなたが顧客に売った契約プランの
     * 上限であり、掲載企業ごとの掲載プラン(PostingPlan)とは別軸。
     * 運営者は変更できず、あなた(パッケージ提供者)が設定する。
     */
    public function canAddMoreCompanies(): bool
    {
        return $this->max_companies === null || Company::count() < $this->max_companies;
    }

    public function canAddMoreJobPostings(): bool
    {
        return $this->max_job_postings === null || JobPosting::count() < $this->max_job_postings;
    }
}
