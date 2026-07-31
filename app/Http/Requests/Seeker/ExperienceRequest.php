<?php

namespace App\Http\Requests\Seeker;

use Illuminate\Foundation\Http\FormRequest;

class ExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('seeker') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'organization_name' => ['required', 'string', 'max:120'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'started_on' => ['nullable', 'date'],
            // 在籍中はチェックボックスで示し、その場合 ended_on は無視する(prepareForValidation を参照)
            'ended_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'organization_name' => '事業所名',
            'job_title' => '職種',
            'started_on' => '在籍開始年月',
            'ended_on' => '在籍終了年月',
            'description' => '業務内容',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->boolean('is_current')) {
            $this->merge(['ended_on' => null]);
        }
    }
}
