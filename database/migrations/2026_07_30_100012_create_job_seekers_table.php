<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 求職者(メディアに会員登録して応募する人)。
 *
 * ⚠ **健康状態・病歴・障害・治療歴・国籍・信条・社会的身分のカラムを作ってはいけない。**
 *    要配慮個人情報の取得を仕組みで防ぐための設計(CLAUDE.md 3.4 / SPEC.md 12.1)。
 *    「あったほうが便利そう」という理由でこれを緩めないこと。
 *
 * 保有資格・職務経歴は T-11 で別テーブルとして追加する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_seekers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('name_kana', 120)->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('tel', 30)->nullable();
            $table->date('birthday')->nullable();

            // 居住地。求人のおすすめ表示に使う。
            $table->foreignId('prefecture_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_seekers');
    }
};
