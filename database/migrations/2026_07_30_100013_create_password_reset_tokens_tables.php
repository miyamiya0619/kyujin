<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * パスワード再設定のトークン。**ユーザー種別ごとに別テーブルにする。**
 *
 * Laravel 既定のトークンテーブルはメールアドレスが主キー。
 * 3 種類のユーザーで 1 つのテーブルを共有すると、同じメールアドレスを持つ
 * 運営者と求職者が同時に再設定を申請したときに互いのトークンを上書きしてしまう。
 * 一人が複数の立場を持つことは実際に起こりうる(運営スタッフが求職者として登録するなど)。
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['admin_password_reset_tokens', 'company_password_reset_tokens', 'job_seeker_password_reset_tokens'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_password_reset_tokens');
        Schema::dropIfExists('company_password_reset_tokens');
        Schema::dropIfExists('job_seeker_password_reset_tokens');
    }
};
