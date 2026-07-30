<?php

namespace App\Http\Controllers\Seeker\Auth;

use App\Http\Controllers\Auth\BaseLoginController;

/** 求職者のログイン。公開サイト内に置く。 */
class LoginController extends BaseLoginController
{
    protected function guard(): string
    {
        return 'seeker';
    }

    protected function view(): string
    {
        return 'seeker.auth.login';
    }

    protected function redirectAfterLogin(): string
    {
        return route('seeker.mypage');
    }

    protected function loginRoute(): string
    {
        return route('seeker.login');
    }
}
