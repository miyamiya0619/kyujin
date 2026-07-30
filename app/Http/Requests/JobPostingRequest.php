<?php

namespace App\Http\Requests;

use App\Models\JobPosting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 求人の登録・編集。運営者・掲載企業の双方で使う。
 *
 * **フォーム項目は固定。** 顧客が項目を追加できる仕組みは作らない(CLAUDE.md 3.3)。
 * workplace_id が自社の事業所であることの検証は、コントローラ側で行う
 * (企業の決め方がガードによって違うため、ここでは扱わない)。
 */
class JobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null || $this->user('company') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'workplace_id' => ['required', 'integer', 'exists:workplaces,id'],
            'title' => ['required', 'string', 'max:120'],

            'job_category_id' => ['nullable', 'integer', 'exists:job_categories,id'],
            'employment_type_id' => ['nullable', 'integer', 'exists:employment_types,id'],

            'salary_type' => ['nullable', Rule::in([
                JobPosting::SALARY_TYPE_MONTHLY,
                JobPosting::SALARY_TYPE_HOURLY,
                JobPosting::SALARY_TYPE_DAILY,
                JobPosting::SALARY_TYPE_ANNUAL,
            ])],
            'salary_min' => ['nullable', 'integer', 'min:0', 'lte:salary_max'],
            'salary_max' => ['nullable', 'integer', 'min:0'],

            'working_hours' => ['nullable', 'string', 'max:2000'],
            'holidays' => ['nullable', 'string', 'max:2000'],
            'benefits' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:5000'],

            'has_night_shift' => ['boolean'],

            'qualification_ids' => ['nullable', 'array'],
            'qualification_ids.*' => ['integer', 'exists:qualifications,id'],

            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'exists:job_features,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'workplace_id' => '事業所',
            'title' => '求人タイトル',
            'job_category_id' => '職種',
            'employment_type_id' => '雇用形態',
            'salary_type' => '給与形態',
            'salary_min' => '給与(下限)',
            'salary_max' => '給与(上限)',
            'working_hours' => '勤務時間',
            'holidays' => '休日・休暇',
            'benefits' => '待遇・福利厚生',
            'description' => '仕事内容',
            'has_night_shift' => '夜勤の有無',
            'qualification_ids' => '必要資格',
            'feature_ids' => 'こだわり条件',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'salary_min.lte' => '給与(下限)は給与(上限)以下にしてください。',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['has_night_shift' => $this->boolean('has_night_shift')]);
    }
}
