<?php

namespace App\Http\Controllers\Seeker\Auth;

use App\Http\Controllers\Auth\BasePasswordResetController;

/** 求職者のパスワード再設定。 */
class PasswordResetController extends BasePasswordResetController
{
    protected function broker(): string
    {
        return 'seekers';
    }

    protected function requestView(): string
    {
        return 'seeker.auth.forgot-password';
    }

    protected function resetView(): string
    {
        return 'seeker.auth.reset-password';
    }

    protected function loginRoute(): string
    {
        return route('seeker.login');
    }
}
