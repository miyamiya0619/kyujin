<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `applications.job_seeker_id` を `cascadeOnDelete` から `nullOnDelete` に変更する。
 *
 * ⚠ **T-12 で作成した際の設計ミス。** 求職者が退会(`Seeker\AccountController::destroy()`)
 * すると `job_seekers` 行が削除されるが、`cascadeOnDelete` のままだと応募
 * (`applications`)とその履歴書スナップショット(`application_resume_snapshots`。
 * `application_id` を経由してカスケード削除される)まで一緒に消えてしまっていた。
 *
 * CLAUDE.md 3.6 の絶対ルール「求職者が退会しても企業側の選考記録が破綻しない」に
 * 反するため、T-17 で発覚し修正した。`job_seeker_id` を nullable にし、
 * 退会時は参照だけを外して応募レコードとスナップショットを残す。
 *
 * doctrine/dbal を依存に追加したくないため、列の NULL 許可への変更は
 * `Blueprint::change()` ではなく生 SQL で行う。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['job_seeker_id']);
        });

        DB::statement('ALTER TABLE applications MODIFY job_seeker_id BIGINT UNSIGNED NULL');

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('job_seeker_id')->references('id')->on('job_seekers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['job_seeker_id']);
        });

        DB::statement('ALTER TABLE applications MODIFY job_seeker_id BIGINT UNSIGNED NOT NULL');

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('job_seeker_id')->references('id')->on('job_seekers')->cascadeOnDelete();
        });
    }
};
