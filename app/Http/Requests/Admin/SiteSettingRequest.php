<?php

namespace App\Http\Requests\Admin;

use App\Support\Theme\ThemeManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * サイト設定の編集(運営者。SPEC.md 5.3)。
 *
 * **フォーム項目は固定。** 顧客が項目を追加できる仕組みは作らない(CLAUDE.md 3.3)。
 * `max_job_postings` / `max_companies`(パッケージ販売プランの上限)はここに含めない。
 * 運営者が変更できる項目ではなく、あなた(パッケージ提供者)が設定するもの
 * (SiteSetting::canAddMoreCompanies() のコメントを参照)。
 */
class SiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:100'],
            'catch_copy' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'theme' => ['required', 'string', Rule::in(app(ThemeManager::class)->available())],
            'theme_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_tel' => ['nullable', 'string', 'max:30'],
            'requires_review' => ['boolean'],
            'enables_member' => ['boolean'],
            'enables_posting_plan' => ['boolean'],
            'enables_external_feed' => ['boolean'],
            'terms_of_service' => ['nullable', 'string', 'max:20000'],
            'privacy_policy' => ['nullable', 'string', 'max:20000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'key_visual' => ['nullable', 'image', 'max:4096'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'site_name' => 'メディア名',
            'catch_copy' => 'キャッチコピー',
            'meta_description' => 'メタディスクリプション',
            'theme' => 'テーマ',
            'theme_color' => 'テーマカラー',
            'contact_email' => '連絡先メールアドレス',
            'contact_tel' => '連絡先電話番号',
            'requires_review' => '審査フロー',
            'enables_member' => '求職者会員機能',
            'enables_posting_plan' => '掲載プランによる制限',
            'enables_external_feed' => 'アグリゲーション連携',
            'terms_of_service' => '利用規約',
            'privacy_policy' => 'プライバシーポリシー',
            'logo' => 'ロゴ',
            'key_visual' => 'キービジュアル',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requires_review' => $this->boolean('requires_review'),
            'enables_member' => $this->boolean('enables_member'),
            'enables_posting_plan' => $this->boolean('enables_posting_plan'),
            'enables_external_feed' => $this->boolean('enables_external_feed'),
        ]);
    }
}
