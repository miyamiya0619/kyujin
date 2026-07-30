<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 掲載企業への掲載プラン割当(SPEC.md 6.1)。
 *
 * 履歴として複数件残る(プラン変更のたびに新しい行を追加する想定)。
 * 「現在有効なプラン」は ends_at が null または未来のものを starts_at 降順で 1 件引く。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_plan_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('posting_plan_id')->constrained()->restrictOnDelete();

            $table->date('starts_at');
            $table->date('ends_at')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_plan_assignments');
    }
};
