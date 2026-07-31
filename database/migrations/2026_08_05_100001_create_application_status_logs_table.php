<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 応募の選考ステータス変更履歴(SPEC.md 8.3)。追記のみで削除・更新はしない(証跡のため)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();

            // 変更した担当者が退職等でアカウントを削除されても、証跡は残す(nullOnDelete)。
            $table->foreignId('company_user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('from_status', 20);
            $table->string('to_status', 20);

            $table->timestamps();

            $table->index(['application_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_logs');
    }
};
