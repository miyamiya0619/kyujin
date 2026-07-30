<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 掲載プランの定義(SPEC.md 6.1)。運営者が自由に作成・編集する。
 */
class PostingPlanRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:60'],
            'max_job_postings' => ['nullable', 'integer', 'min:1'],
            'posting_duration_days' => ['nullable', 'integer', 'min:1'],
            'max_workplaces' => ['nullable', 'integer', 'min:1'],
            'is_featured' => ['boolean'],
            'monthly_price' => ['nullable', 'integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'プラン名',
            'max_job_postings' => '同時掲載可能件数',
            'posting_duration_days' => '掲載期間(日数)',
            'max_workplaces' => '事業所登録数の上限',
            'is_featured' => '上位表示',
            'monthly_price' => '月額表示価格',
            'is_enabled' => '有効',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'is_enabled' => $this->boolean('is_enabled', true),
        ]);
    }
}
