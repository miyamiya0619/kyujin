<?php

namespace App\Http\Requests\Admin;

use App\Models\CompanyUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 掲載企業の担当者の追加。運営者が使う。
 * パスワードは受け取らない。本人が招待メールのリンクから設定する。
 */
class CompanyUserRequest extends FormRequest
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
            // メールはシステム全体で一意。別の企業に同じメールは登録できない。
            'email' => ['required', 'email', 'max:255', Rule::unique('company_users', 'email')],
            'role' => ['required', Rule::in([CompanyUser::ROLE_OWNER, CompanyUser::ROLE_MEMBER])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => '担当者名',
            'email' => 'メールアドレス',
            'role' => '権限',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'このメールアドレスは既に別の担当者として登録されています。',
        ];
    }
}
