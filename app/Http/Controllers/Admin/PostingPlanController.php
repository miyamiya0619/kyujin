<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostingPlanRequest;
use App\Models\PostingPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * 掲載プランの定義(運営者)。SPEC.md 6.1。
 * 掲載企業への割当は Admin\CompanyController の詳細画面から行う。
 */
class PostingPlanController extends Controller
{
    public function index(): View
    {
        return view('admin.posting-plans.index', [
            'postingPlans' => PostingPlan::query()->ordered()->withCount('assignments')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.posting-plans.create', ['postingPlan' => new PostingPlan(['is_enabled' => true])]);
    }

    public function store(PostingPlanRequest $request): RedirectResponse
    {
        PostingPlan::create($request->validated());

        return redirect()->route('admin.posting-plans.index')->with('status', '掲載プランを作成しました。');
    }

    public function edit(PostingPlan $postingPlan): View
    {
        return view('admin.posting-plans.edit', ['postingPlan' => $postingPlan]);
    }

    public function update(PostingPlanRequest $request, PostingPlan $postingPlan): RedirectResponse
    {
        $postingPlan->update($request->validated());

        return redirect()->route('admin.posting-plans.index')->with('status', '掲載プランを更新しました。');
    }
}
