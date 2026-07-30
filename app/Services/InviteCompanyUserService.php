<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Notifications\CompanyUserInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * 掲載企業の担当者を作成し、パスワード設定の案内を送る。
 */
class InviteCompanyUserService
{
    /**
     * @param  array{name: string, email: string, role: string}  $attributes
     */
    public function invite(Company $company, array $attributes): CompanyUser
    {
        $user = DB::transaction(function () use ($company, $attributes) {
            return $company->users()->create([
                ...$attributes,
                // 本人がリンクから設定するまで使えないパスワードを入れておく。
                // 空にできないため、推測不能なランダム値で埋める。
                'password' => Str::password(40),
                'is_active' => true,
            ]);
        });

        $this->sendInvitation($user);

        return $user;
    }

    /**
     * 招待メールを送り直す。届かなかった・期限が切れたときに使う。
     */
    public function sendInvitation(CompanyUser $user): void
    {
        $token = Password::broker('companies')->createToken($user);

        $user->notify(new CompanyUserInvitationNotification(
            $token,
            $user->company->name,
        ));
    }
}
