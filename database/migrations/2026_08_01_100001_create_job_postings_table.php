<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 求人(SPEC.md 8.2)。
 *
 * status は審査フロー(T-08)の状態遷移を表す:
 *   draft(下書き) → pending(審査待ち) → published(公開中) / rejected(差戻し)
 *   published → closed(掲載終了・掲載期限切れ)
 *
 * ⚠ status = published 以外を公開サイト・検索結果・フィード・サイトマップに
 *   出してはいけない(CLAUDE.md 3.5)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workplace_id')->constrained()->cascadeOnDelete();

            $table->string('title', 120);
            $table->foreignId('job_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employment_type_id')->nullable()->constrained()->nullOnDelete();

            $table->string('salary_type', 10)->nullable(); // monthly / hourly / daily / annual
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();

            $table->text('working_hours')->nullable();
            $table->text('holidays')->nullable();
            $table->text('benefits')->nullable();
            $table->text('description')->nullable();

            // 夜勤の有無。介護では最重要の絞り込み条件のため独立カラムにする(SPEC.md 8.2)。
            $table->boolean('has_night_shift')->default(false);

            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->boolean('is_featured')->default(false);

            // アグリゲーション媒体(Indeed 等)への配信許可。T-14 で使う。
            $table->boolean('allow_external_feed')->default(false);

            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('application_count')->default(0);

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['status', 'published_at']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
