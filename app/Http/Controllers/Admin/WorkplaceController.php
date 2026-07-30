<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesWorkplaces;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkplaceRequest;
use App\Models\Company;
use App\Models\Workplace;
use App\Services\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 事業所の管理(運営者)。運営者は任意の掲載企業の事業所を代行編集できる。
 *
 * ルートは `companies/{company}/workplaces/{workplace}` の順。
 * 公開アクションの引数もこの順で受け取り、実処理はトレイトの `do*` に委譲する
 * (ManagesWorkplaces のコメントを参照。引数順を変えると暗黙バインディングが壊れる)。
 */
class WorkplaceController extends Controller
{
    use ManagesWorkplaces;

    public function __construct(ImageUploadService $images)
    {
        $this->images = $images;
    }

    public function index(Company $company): View
    {
        return $this->doIndex($company);
    }

    public function create(Company $company): View
    {
        return $this->doCreate($company);
    }

    public function store(WorkplaceRequest $request, Company $company): RedirectResponse
    {
        return $this->doStore($request, $company);
    }

    public function edit(Company $company, Workplace $workplace): View
    {
        return $this->doEdit($company, $workplace);
    }

    public function update(WorkplaceRequest $request, Company $company, Workplace $workplace): RedirectResponse
    {
        return $this->doUpdate($request, $company, $workplace);
    }

    public function destroy(Company $company, Workplace $workplace): RedirectResponse
    {
        return $this->doDestroy($company, $workplace);
    }

    /** 運営者は URL で指定された企業をそのまま対象にする。 */
    protected function targetCompany(?Company $routeCompany = null): Company
    {
        return $routeCompany;
    }

    protected function viewPrefix(): string
    {
        return 'admin.workplaces';
    }

    protected function redirectRoute(Company $company): string
    {
        return route('admin.companies.workplaces.index', $company);
    }
}
