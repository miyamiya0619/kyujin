<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 求人の審査履歴(SPEC.md 7章)。運営者が承認・差戻しした記録を全件残す。
 *
 * 「誰が・いつ・何を判断したか」の証跡であり、削除・更新はしない(追記のみ)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posting_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained()->nullOnDelete();

            // approved / rejected / auto_approved(審査OFF設定での自動承認)
            $table->string('action', 20);

            // 差戻し理由。承認時は null。
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->index(['job_posting_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posting_reviews');
    }
};
