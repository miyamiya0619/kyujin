<?php

namespace App\Http\Requests;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 事業所の登録・編集。運営者・掲載企業の双方で使う。
 * 権限の判定(自社かどうか)は呼び出し側のコントローラで行う。
 */
class WorkplaceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'facility_type_id' => ['nullable', 'integer', 'exists:facility_types,id'],

            'postal_code' => ['nullable', 'string', 'max:8', 'regex:/^\d{3}-?\d{4}$/'],
            'prefecture_id' => ['nullable', 'integer', 'exists:prefectures,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'access' => ['nullable', 'string', 'max:255'],

            'description' => ['nullable', 'string', 'max:5000'],

            'photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => '事業所名',
            'facility_type_id' => '施設形態',
            'postal_code' => '郵便番号',
            'prefecture_id' => '都道府県',
            'city_id' => '市区町村',
            'address' => '住所',
            'access' => 'アクセス',
            'description' => '施設紹介',
            'photo' => '施設写真',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'postal_code.regex' => '郵便番号は 1234567 または 123-4567 の形式で入力してください。',
            'photo.max' => '施設写真は 5MB 以下のファイルを選んでください。',
            'photo.mimes' => '施設写真は JPEG・PNG・WebP のいずれかを選んでください。',
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
