<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 監査ログ(SPEC.md 8.2)。
 *
 * 運営者・掲載企業・求職者の 3 ガードにまたがる操作者を 1 カラムで表すため、
 * `actor_type` + `actor_id` のポリモーフィックな組にする(外部キー制約は張らない)。
 *
 * 記録対象(CLAUDE.md / SPEC.md 5.3): 審査の承認/差戻し、応募者情報の閲覧・CSV出力、
 * 掲載プランの変更、サイト設定の変更、データ削除。
 * T-13 時点では応募者の CSV 出力のみを記録し、他の記録対象の実装漏れ確認は T-16 で行う。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->string('actor_type', 20); // admin / company / seeker
            $table->unsignedBigInteger('actor_id');

            $table->string('action', 50);

            $table->string('target_type', 50)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();

            $table->string('ip_address', 45)->nullable();

            // 追記のみで更新しない証跡のため created_at だけを持つ。
            $table->timestamp('created_at')->useCurrent();

            $table->index(['actor_type', 'actor_id']);
            $table->index(['target_type', 'target_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
