<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 掲載プラン(SPEC.md 6.1)。運営者が自由に定義し、掲載企業に割り当てる。
 *
 * 「あなたが顧客に売るパッケージ販売プラン」(SPEC.md 6.2)とは別物。
 * こちらは「顧客(メディア運営者)が掲載企業に売るプラン」。
 * 月額表示価格は表示のみで、課金・請求はシステム外(SPEC.md 1.4)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posting_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);

            // null は無制限。運用しながら必ず変わる値のため、コードに埋め込まない。
            $table->unsignedInteger('max_job_postings')->nullable();
            $table->unsignedInteger('posting_duration_days')->nullable();
            $table->unsignedInteger('max_workplaces')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('monthly_price')->nullable();

            $table->boolean('is_enabled')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posting_plans');
    }
};
