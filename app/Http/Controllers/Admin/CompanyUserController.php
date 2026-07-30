<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyUserRequest;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Services\InviteCompanyUserService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 掲載企業の担当者アカウントの発行(運営者)。
 *
 * 担当者の作成は運営者だけが行う(SPEC.md 5.3)。
 * 掲載企業側からは追加できない。
 */
class CompanyUserController extends Controller
{
    public function __construct(private readonly InviteCompanyUserService $inviter) {}

    public function store(CompanyUserRequest $request, Company $company): RedirectResponse
    {
        $user = $this->inviter->invite($company, $request->validated());

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('status', "{$user->name} さんに招待メールを送りました。");
    }

    /**
     * 招待メールの再送。届かなかった・リンクの期限が切れたときに使う。
     */
    public function resend(Company $company, CompanyUser $user): RedirectResponse
    {
        $this->ensureBelongsTo($company, $user);

        $this->inviter->sendInvitation($user);

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('status', "{$user->name} さんに招待メールを再送しました。");
    }

    /**
     * 担当者の有効・無効を切り替える。
     * 削除ではなく無効化にするのは、操作ログから参照されるため。
     */
    public function toggle(Company $company, CompanyUser $user): RedirectResponse
    {
        $this->ensureBelongsTo($company, $user);

        $user->update(['is_active' => ! $user->is_active]);

        $label = $user->is_active ? '有効' : '無効';

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('status', "{$user->name} さんのアカウントを{$label}にしました。");
    }

    /**
     * URL を組み替えて別の企業の担当者を操作されるのを防ぐ。
     * 403 ではなく 404 を返す(存在自体を知らせない)。
     */
    private function ensureBelongsTo(Company $company, CompanyUser $user): void
    {
        if ($user->company_id !== $company->id) {
            throw new NotFoundHttpException;
        }
    }
}
