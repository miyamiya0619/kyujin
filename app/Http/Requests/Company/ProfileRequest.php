<?php

namespace App\Http\Requests\Company;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 掲載企業が自社の情報を編集する。
 *
 * 運営者用の CompanyRequest と違い **status を受け取らない。**
 * 掲載企業が自分で掲載停止を解除できてしまうため。
 */
class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('company') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'name_kana' => ['nullable', 'string', 'max:240'],
            'description' => ['nullable', 'string', 'max:5000'],

            'postal_code' => ['nullable', 'string', 'max:8', 'regex:/^\d{3}-?\d{4}$/'],
            'prefecture_id' => ['nullable', 'integer', 'exists:prefectures,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address' => ['nullable', 'string', 'max:255'],

            'tel' => ['nullable', 'string', 'max:30'],
            'website_url' => ['nullable', 'url', 'max:255'],

            'logo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => '企業名',
            'name_kana' => '企業名(ふりがな)',
            'description' => '企業紹介',
            'postal_code' => '郵便番号',
            'prefecture_id' => '都道府県',
            'city_id' => '市区町村',
            'address' => '住所',
            'tel' => '電話番号',
            'website_url' => 'ウェブサイト',
            'logo' => 'ロゴ画像',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'postal_code.regex' => '郵便番号は 1234567 または 123-4567 の形式で入力してください。',
            'logo.max' => 'ロゴ画像は 5MB 以下のファイルを選んでください。',
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
