<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 求職者会員登録にメール認証を追加する(TASKS.md T-27)。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        // この機能を導入する前に登録済みの求職者は、遡ってメール認証を
        // 求めない(登録時点で本人確認を求めていなかった以上、後から
        // ロックアウトするのは筋が違う)。作成日時をもって認証済みとみなす。
        DB::table('job_seekers')->update(['email_verified_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });
    }
};
