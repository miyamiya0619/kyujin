<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * サイト設定の行が存在することを保証する。
 *
 * **値は一切上書きしない。** デプロイのたびにシーダーを再実行するため、
 * ここで値を入れると顧客が管理画面で設定した内容が毎回消える。
 *
 * 初期値はマイグレーションのカラム既定値で定義してある。
 * シーダーより先に Web リクエストが来て SiteSetting::current() が行を作る場合もあるが、
 * どちらの経路でも同じ既定値になるようにしてある。
 */
class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::current();
    }
}
