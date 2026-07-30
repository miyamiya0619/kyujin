<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Auth\BaseLoginController;

/** 運営者のログイン。 */
class LoginController extends BaseLoginController
{
    protected function guard(): string
    {
        return 'admin';
    }

    protected function view(): string
    {
        return 'admin.auth.login';
    }

    protected function redirectAfterLogin(): string
    {
        return route('admin.dashboard');
    }

    protected function loginRoute(): string
    {
        return route('admin.login');
    }

    /** 無効化されたアカウントではログインできない。 */
    protected function extraCredentials(): array
    {
        return ['is_active' => true];
    }
}
