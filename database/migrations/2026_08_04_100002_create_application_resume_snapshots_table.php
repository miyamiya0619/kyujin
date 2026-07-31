<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 応募時点の履歴書の写し(SPEC.md 8.2)。
 *
 * 応募の瞬間に求職者のプロフィール・保有資格・職務経歴を JSON に固めて保存する。
 * 応募後に本人がプロフィールを直しても、ここの内容は変わらない
 * (選考の公平性と証跡。求職者が退会しても掲載企業の選考記録が破綻しないようにする)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_resume_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('payload');
            $table->timestamp('snapshot_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_resume_snapshots');
    }
};
