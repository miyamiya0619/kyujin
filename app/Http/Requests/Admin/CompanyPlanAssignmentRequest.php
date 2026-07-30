<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CompanyPlanAssignmentRequest extends FormRequest
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
            'posting_plan_id' => ['required', 'integer', 'exists:posting_plans,id'],
            'starts_at' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'posting_plan_id' => '掲載プラン',
            'starts_at' => '適用開始日',
        ];
    }
}
