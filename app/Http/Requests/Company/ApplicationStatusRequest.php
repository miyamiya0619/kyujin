<?php

namespace App\Http\Requests\Company;

use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(Application::STATUS_LABELS))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['status' => '選考ステータス'];
    }
}
