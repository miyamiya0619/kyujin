<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Auth\BasePasswordResetController;

/** 運営者のパスワード再設定。 */
class PasswordResetController extends BasePasswordResetController
{
    protected function broker(): string
    {
        return 'admins';
    }

    protected function requestView(): string
    {
        return 'admin.auth.forgot-password';
    }

    protected function resetView(): string
    {
        return 'admin.auth.reset-password';
    }

    protected function loginRoute(): string
    {
        return route('admin.login');
    }
}
