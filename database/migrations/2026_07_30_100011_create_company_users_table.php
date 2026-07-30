<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 掲載企業の担当者。自社の求人と応募者だけを扱える。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            // メールはシステム全体で一意にする。
            // 企業内で一意にすると、ログイン時に企業を選ばせる必要が出てUXが悪くなる。
            $table->string('email')->unique();
            $table->string('password');
            // owner: 担当者の追加・削除ができる / member: 求人と応募者のみ
            $table->string('role', 20)->default('member');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_users');
    }
};
