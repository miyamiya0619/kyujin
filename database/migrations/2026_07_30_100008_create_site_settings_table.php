<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * このメディア(= この環境)の設定。**必ず 1 行だけ持つ。**
 *
 * 顧客ごとの違いを吸収してよいのは「設定・DB・テーマ」の 3 つだけで、
 * このテーブルはその「設定」にあたる(CLAUDE.md 3.1)。
 * 顧客固有の分岐をコードに書きたくなったら、まずここで表現できないかを考えること。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            // --- メディアの基本情報 ---
            // 初期値はここで定義する。シーダーからも Web リクエストからも
            // 同じ既定値で行が作られるようにするため(SiteSettingSeeder を参照)。
            $table->string('site_name', 100)->default('求人メディア');
            $table->string('catch_copy', 200)->nullable()->default('介護・医療・福祉の求人を探す');
            $table->string('meta_description', 300)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('key_visual_path')->nullable();

            // --- 見た目 ---
            // テーマ名。resources/views/themes/{theme} と public/themes/{theme} に対応する。
            $table->string('theme', 50)->default('default');
            // 標準テーマの色だけ変えたい顧客向け。テーマを個別制作する場合は使わない。
            $table->string('theme_color', 20)->default('#2563eb');

            // --- 連絡先 ---
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_tel', 30)->nullable();

            // --- 機能のオンオフ ---
            // 審査フロー。OFF にすると掲載企業の提出が即公開になる(SPEC.md 7章)。
            $table->boolean('requires_review')->default(true);
            // 求職者の会員機能。OFF にすると都度応募のみになる。
            $table->boolean('enables_member')->default(true);
            // 掲載プランによる掲載件数の制限。
            $table->boolean('enables_posting_plan')->default(true);

            // --- パッケージ販売プランの上限(SPEC.md 6.2)---
            // 顧客が契約したプランの上限。null は無制限。
            // 運営者は変更できず、あなた(パッケージ提供者)が設定する。
            $table->unsignedInteger('max_job_postings')->nullable();
            $table->unsignedInteger('max_companies')->nullable();

            // --- 規約類 ---
            $table->text('terms_of_service')->nullable();
            $table->text('privacy_policy')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
