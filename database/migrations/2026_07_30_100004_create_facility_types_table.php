<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            // 入所系 / 通所系 / 訪問系 / 複合 / 相談・支援 / 障害福祉 / 医療・保育
            $table->string('category', 20);
            $table->string('name', 60);
            // 略称。「特別養護老人ホーム」に対する「特養」など、現場で使われる呼び方。
            // 検索のヒット率を上げるために持つ。
            $table->string('short_name', 30)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_types');
    }
};
