<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * 掲載企業のダッシュボード。
 * T-09 で掲載プランの残枠、T-13 で未対応の応募件数を表示する。
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('company.dashboard');
    }
}
