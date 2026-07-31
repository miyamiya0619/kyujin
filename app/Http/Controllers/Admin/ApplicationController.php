<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Company;
use App\Services\ApplicationSearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * 応募の横断確認(運営者。SPEC.md 5.3)。
 *
 * 全掲載企業の応募状況を閲覧できる。運営者はステータス変更・メモ・CSV出力は行わない
 * (それらは応募を受け取った掲載企業自身が行う。TASKS.md T-13)。
 */
class ApplicationController extends Controller
{
    public function index(Request $request, ApplicationSearchService $search): View
    {
        return view('admin.applications.index', [
            'applications' => $search->search($request),
            'companies' => Company::orderBy('name')->get(),
            'statusOptions' => Application::STATUS_LABELS,
        ]);
    }
}
