<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 運営者(メディア運営者 = 顧客のスタッフ)。
 * 求人の審査・掲載企業の管理・サイト設定を行う、この環境で最も強い権限を持つ。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('email')->unique();
            $table->string('password');
            // 退職者のアカウントを消さずに止められるようにする。
            // 審査履歴から参照されるため、削除ではなく無効化で運用する。
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
