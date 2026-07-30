<?php

namespace App\Http\Controllers\Company\Auth;

use App\Http\Controllers\Auth\BaseLoginController;

/** 掲載企業の担当者のログイン。 */
class LoginController extends BaseLoginController
{
    protected function guard(): string
    {
        return 'company';
    }

    protected function view(): string
    {
        return 'company.auth.login';
    }

    protected function redirectAfterLogin(): string
    {
        return route('company.dashboard');
    }

    protected function loginRoute(): string
    {
        return route('company.login');
    }

    /** 無効化された担当者ではログインできない。 */
    protected function extraCredentials(): array
    {
        return ['is_active' => true];
    }
}
