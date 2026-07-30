<?php

use App\Models\AdminUser;
use App\Models\CompanyUser;
use App\Models\JobSeeker;

/*
|--------------------------------------------------------------------------
| 認証の構成
|--------------------------------------------------------------------------
| この製品には 3 種類のユーザーがいて、それぞれ完全に独立したログインを持つ。
|
|   admin   運営者(メディア運営者 = 顧客のスタッフ)   /admin/login
|   company 掲載企業の担当者                          /company/login
|   seeker  求職者                                    /login(公開サイト内)
|
| ガード名が違えばセッションのキーも分かれるため、同じブラウザで
| 3 つ同時にログインできる。運営者が動作確認するときに便利。
|
| ⚠ ミドルウェアでは必ずガードを明示すること(auth:admin / auth:company / auth:seeker)。
|   無指定にすると既定ガード(seeker)が使われ、権限の穴になる。
|
| パスワード再設定のトークンはユーザー種別ごとに別テーブルにしている。
| 同じメールアドレスを持つ運営者と求職者が互いのトークンを上書きしないようにするため。
*/

return [

    'defaults' => [
        // 公開サイトが最も多く使われるため求職者を既定にする。
        'guard' => 'seeker',
        'passwords' => 'seekers',
    ],

    'guards' => [
        'admin' => [
            'driver' => 'session',
            'provider' => 'admin_users',
        ],

        'company' => [
            'driver' => 'session',
            'provider' => 'company_users',
        ],

        'seeker' => [
            'driver' => 'session',
            'provider' => 'job_seekers',
        ],
    ],

    'providers' => [
        'admin_users' => [
            'driver' => 'eloquent',
            'model' => AdminUser::class,
        ],

        'company_users' => [
            'driver' => 'eloquent',
            'model' => CompanyUser::class,
        ],

        'job_seekers' => [
            'driver' => 'eloquent',
            'model' => JobSeeker::class,
        ],
    ],

    'passwords' => [
        'admins' => [
            'provider' => 'admin_users',
            'table' => 'admin_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'companies' => [
            'provider' => 'company_users',
            'table' => 'company_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'seekers' => [
            'provider' => 'job_seekers',
            'table' => 'job_seeker_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
