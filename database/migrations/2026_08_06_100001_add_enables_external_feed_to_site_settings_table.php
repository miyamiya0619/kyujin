<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * アグリゲーション連携(SPEC.md 10章)の配信可否。運営者の設定が優先される
 * グローバルなスイッチで、掲載企業ごとの `job_postings.allow_external_feed` より上位。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('enables_external_feed')->default(true)->after('enables_posting_plan');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('enables_external_feed');
        });
    }
};
