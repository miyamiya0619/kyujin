<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * セッション。本番のセッションドライバは database(エックスサーバーに Redis が無いため)。
 *
 * Laravel 既定の users / password_reset_tokens は作らない。
 * この製品には 3 種類のユーザー(運営者・掲載企業の担当者・求職者)がいて、
 * 単一の users テーブルでは表現できないため、それぞれ専用のテーブルを持つ。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // 既定ガード(seeker)でログイン中のユーザー ID が入る。
            // 3 ガードあるため一意に定まらず、あくまで参考値として扱うこと。
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
