<?php

namespace App\Http\Requests\Seeker;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 求職者のプロフィール編集。
 *
 * **フォーム項目は固定。** 健康状態・病歴・障害などの入力欄を作らない(CLAUDE.md 3.4)。
 */
class ProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:60'],
            'name_kana' => ['nullable', 'string', 'max:120'],
            'tel' => ['nullable', 'string', 'max:30'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'prefecture_id' => ['nullable', 'integer', 'exists:prefectures,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'qualification_ids' => ['nullable', 'array'],
            'qualification_ids.*' => ['integer', 'exists:qualifications,id'],
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
            'tel' => '電話番号',
            'birthday' => '生年月日',
            'prefecture_id' => '都道府県',
            'city_id' => '市区町村',
            'qualification_ids' => '保有資格',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('prefecture_id')) {
            $this->merge(['city_id' => null]);
        }
    }

    protected function passedValidation(): void
    {
        if ($this->filled('city_id') && $this->filled('prefecture_id')) {
            $belongs = City::query()
                ->whereKey($this->integer('city_id'))
                ->where('prefecture_id', $this->integer('prefecture_id'))
                ->exists();

            if (! $belongs) {
                $this->validator->errors()->add('city_id', '市区町村が都道府県と一致しません。');
                $this->failedValidation($this->validator);
            }
        }
    }
}
