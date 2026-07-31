<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 求職者の職務経歴(SPEC.md 8.2)。事業所名・職種・在籍期間・業務内容。
 * 複数件を持ち、求職者が任意の順序に並び替えられる。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_seeker_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_seeker_id')->constrained()->cascadeOnDelete();

            $table->string('organization_name', 120);
            $table->string('job_title', 120)->nullable();
            $table->date('started_on')->nullable();
            // 在籍中は null。
            $table->date('ended_on')->nullable();
            $table->text('description')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['job_seeker_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_seeker_experiences');
    }
};
