<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 掲載企業(メディアに求人を掲載する介護事業者)。顧客の顧客にあたる。
 *
 * このテーブル自体は T-05 で管理画面を作るが、company_users が参照するため
 * 認証基盤(T-04)の時点で作成する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('name_kana', 240)->nullable();
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();

            // 本社所在地。求人の検索対象になるのは事業所(workplaces)の住所であり、
            // ここは表示用。市区町村マスタが未整備でも登録できるよう nullable にしている。
            $table->string('postal_code', 8)->nullable();
            $table->foreignId('prefecture_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address', 255)->nullable();

            $table->string('tel', 30)->nullable();
            $table->string('website_url', 255)->nullable();

            // active: 掲載可能 / suspended: 運営者が一時停止 / archived: 契約終了
            $table->string('status', 20)->default('active');

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
