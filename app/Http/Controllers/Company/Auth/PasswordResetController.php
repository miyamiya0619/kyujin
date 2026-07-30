<?php

namespace App\Http\Controllers\Company\Auth;

use App\Http\Controllers\Auth\BasePasswordResetController;

/** 掲載企業の担当者のパスワード再設定。 */
class PasswordResetController extends BasePasswordResetController
{
    protected function broker(): string
    {
        return 'companies';
    }

    protected function requestView(): string
    {
        return 'company.auth.forgot-password';
    }

    protected function resetView(): string
    {
        return 'company.auth.reset-password';
    }

    protected function loginRoute(): string
    {
        return route('company.login');
    }
}
