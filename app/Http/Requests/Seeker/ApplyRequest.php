<?php

namespace App\Http\Requests\Seeker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * 求人への応募。
 *
 * ログイン中は応募メッセージのみ。未ログインは会員登録項目を併せて受け取り、
 * 会員登録と応募を 1 回の送信で完了させる(TASKS.md T-12)。
 *
 * **フォーム項目は固定。** 追加する仕組みを作らない(CLAUDE.md 3.3)。
 */
class ApplyRequest extends FormRequest
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
        $rules = [
            'message' => ['nullable', 'string', 'max:1000'],
        ];

        if (auth('seeker')->guest()) {
            $rules += [
                'name' => ['required', 'string', 'max:60'],
                'name_kana' => ['nullable', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:255', 'unique:job_seekers,email'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'message' => '応募メッセージ',
            'name' => 'お名前',
            'name_kana' => 'お名前(ふりがな)',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
        ];
    }
}
