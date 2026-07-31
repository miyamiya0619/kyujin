<?php

namespace App\Http\Requests\Seeker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * 求職者の会員登録。
 *
 * **フォーム項目は固定。** 健康状態・病歴・障害などの入力欄を作らない
 * (CLAUDE.md 3.3 / 3.4)。項目を追加する仕組みも作らない。
 */
class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:60'],
            'name_kana' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:job_seekers,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'tel' => ['nullable', 'string', 'max:30'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'prefecture_id' => ['nullable', 'integer', 'exists:prefectures,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'お名前',
            'name_kana' => 'お名前(ふりがな)',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'tel' => '電話番号',
            'birthday' => '生年月日',
            'prefecture_id' => '都道府県',
            'city_id' => '市区町村',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('prefecture_id')) {
            $this->merge(['city_id' => null]);
        }
    }
}
