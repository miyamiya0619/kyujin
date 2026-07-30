<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 事業所・勤務地(SPEC.md 8.2)。求人はここに紐づく。
 *
 * 介護事業者は複数拠点を持つのが常態。求人に住所を直接持たせると
 * 同じ事業所の求人を作るたびに住所入力が発生するため、事業所を独立させている。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workplaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_type_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name', 120);
            $table->string('postal_code', 8)->nullable();
            $table->foreignId('prefecture_id')->nullable()->constrained()->nullOnDelete();

            // city は文字列ではなくマスタ参照にする。表記ゆれで市区町村の
            // 絞り込み検索が成立しなくなるため(SPEC.md 8.2)。
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();

            // 番地以降の自由入力(市区町村より後ろの部分)
            $table->string('address', 255)->nullable();

            // 最寄駅・交通手段。求人検索の絞り込みには使わず表示のみ。
            $table->string('access', 255)->nullable();

            $table->string('photo_path')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['company_id']);
            $table->index(['prefecture_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workplaces');
    }
};
