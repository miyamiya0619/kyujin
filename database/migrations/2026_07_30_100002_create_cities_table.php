<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prefecture_id')->constrained()->cascadeOnDelete();
            // 全国地方公共団体コード(5桁)。総務省の公式コードに合わせる。
            $table->string('code', 6)->unique();
            $table->string('name', 40);
            $table->string('name_kana', 80);
            // 顧客が管理する項目
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['prefecture_id', 'is_enabled', 'sort_order'], 'cities_pref_enabled_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
