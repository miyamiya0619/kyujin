<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 応募(SPEC.md 8.2)。
 *
 * `company_id` は `job_posting_id` から辿れるが、掲載企業の応募一覧(T-13)で
 * 求人を経由せず直接絞り込めるように非正規化して持たせる。
 *
 * 同一求職者が同じ求人に重複応募できないよう一意制約を張る(CLAUDE.md 3.6)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_seeker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('status', 20)->default('new');
            $table->text('message')->nullable();

            // direct / indeed / kyujinbox / stanby / google(SPEC.md 10.2)
            $table->string('referrer_source', 20)->default('direct');

            $table->timestamp('applied_at');
            $table->timestamps();

            $table->unique(['job_posting_id', 'job_seeker_id']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
