<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prefectures', function (Blueprint $table) {
            $table->id();
            // 全国地方公共団体コードの都道府県コード(01〜47)。
            // シーダーの再実行時にこのコードで既存行を特定する。
            $table->string('code', 2)->unique();
            $table->string('name', 20);
            $table->string('name_kana', 40);
            // 地方区分(北海道 / 東北 / 関東 / 中部 / 近畿 / 中国 / 四国 / 九州・沖縄)。
            // 公開サイトの「エリアから探す」でグループ表示するために持つ。
            $table->string('region', 20);
            // 以下 2 つは顧客が管理する項目。シーダーの再実行で上書きしない。
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prefectures');
    }
};
