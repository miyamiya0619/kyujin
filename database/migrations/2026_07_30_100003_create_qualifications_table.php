<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            // 製品が定める安定した識別子。シーダーの再実行時にこの値で既存行を特定する。
            // コード内から特定の資格を参照するときにも使う(例: 資格不問)。
            $table->string('code', 50)->unique();
            // 介護 / 福祉 / 医療 / リハビリ / その他 / 無資格
            $table->string('category', 20);
            $table->string('name', 60);
            // 顧客が管理する項目。シーダーの再実行で上書きしない。
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualifications');
    }
};
