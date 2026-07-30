<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\ManagesWorkplaces;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkplaceRequest;
use App\Models\Company;
use App\Models\Workplace;
use App\Services\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 事業所の管理(掲載企業)。
 *
 * **URL に企業 ID を含めない**(CLAUDE.md 3.8)。対象は常にログイン中の担当者の所属企業。
 * ルートパラメータは `{workplace}` のみなので、公開アクションも企業を受け取らない。
 */
class WorkplaceController extends Controller
{
    use ManagesWorkplaces;

    public function __construct(ImageUploadService $images)
    {
        $this->images = $images;
    }

    public function index(): View
    {
        return $this->doIndex(null);
    }

    public function create(): View
    {
        return $this->doCreate(null);
    }

    public function store(WorkplaceRequest $request): RedirectResponse
    {
        return $this->doStore($request, null);
    }

    public function edit(Workplace $workplace): View
    {
        return $this->doEdit(null, $workplace);
    }

    public function update(WorkplaceRequest $request, Workplace $workplace): RedirectResponse
    {
        return $this->doUpdate($request, null, $workplace);
    }

    public function destroy(Workplace $workplace): RedirectResponse
    {
        return $this->doDestroy(null, $workplace);
    }

    /** $routeCompany は使わない。対象は常にログイン中の担当者の所属企業。 */
    protected function targetCompany(?Company $routeCompany = null): Company
    {
        return auth('company')->user()->company;
    }

    protected function viewPrefix(): string
    {
        return 'company.workplaces';
    }

    protected function redirectRoute(Company $company): string
    {
        return route('company.workplaces.index');
    }
}
