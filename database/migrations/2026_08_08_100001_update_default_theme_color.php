<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 標準テーマのブランドカラーを更新(UI 刷新)。
 *
 * 列のデフォルト値を変更し、まだ運営者がカスタマイズしていない
 * (旧デフォルト値 `#2563eb` のままの)行だけ新しい値に更新する。
 * 既にテーマカラーを変更済みの環境の設定は上書きしない。
 *
 * doctrine/dbal を依存に追加したくないため、`Blueprint::change()` ではなく
 * 生 SQL で列のデフォルト値を変更する(T-17 の FK 変更と同じ方針)。
 */
return new class extends Migration
{
    private const OLD_DEFAULT = '#2563eb';

    private const NEW_DEFAULT = '#2F5D50';

    public function up(): void
    {
        DB::statement("ALTER TABLE site_settings MODIFY theme_color VARCHAR(20) NOT NULL DEFAULT '".self::NEW_DEFAULT."'");

        DB::table('site_settings')
            ->where('theme_color', self::OLD_DEFAULT)
            ->update(['theme_color' => self::NEW_DEFAULT]);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE site_settings MODIFY theme_color VARCHAR(20) NOT NULL DEFAULT '".self::OLD_DEFAULT."'");

        DB::table('site_settings')
            ->where('theme_color', self::NEW_DEFAULT)
            ->update(['theme_color' => self::OLD_DEFAULT]);
    }
};
