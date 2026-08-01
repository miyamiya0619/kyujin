<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * 監査ログの閲覧(運営者。SPEC.md 5.3)。
 *
 * 操作者・期間・対象で絞り込める。ログ自体は追記のみで、ここからは編集・削除できない。
 */
class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->when($request->filled('actor_type'), fn ($q) => $q
                ->where('actor_type', $request->string('actor_type')->toString()))
            ->when($request->filled('action'), fn ($q) => $q
                ->where('action', $request->string('action')->toString()))
            ->when($request->filled('from'), fn ($q) => $q
                ->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q
                ->whereDate('created_at', '<=', $request->date('to')))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
