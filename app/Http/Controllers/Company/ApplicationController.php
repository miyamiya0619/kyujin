<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\ApplicationNoteRequest;
use App\Http\Requests\Company\ApplicationStatusRequest;
use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\AuditLog;
use App\Models\Company;
use App\Services\ApplicationCsvExporter;
use App\Services\ApplicationSearchService;
use App\Services\ChangeApplicationStatusService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 応募者管理(掲載企業。TASKS.md T-13)。
 *
 * **URL に企業 ID を含めない**(CLAUDE.md 3.8)。対象は常にログイン中の担当者の所属企業。
 * 応募自体は求人経由で ID を受け取らざるを得ないため、必ず自社に属することを確認する。
 */
class ApplicationController extends Controller
{
    public function index(Request $request, ApplicationSearchService $search): View
    {
        $company = $this->company();
        $applications = $search->search($request, $company);

        return view('company.applications.index', [
            'applications' => $applications,
            'newCount' => $applications->where('status', Application::STATUS_NEW)->count(),
            'jobPostings' => $company->jobPostings()->orderBy('title')->get(),
            'workplaces' => $company->workplaces()->orderBy('name')->get(),
            'statusOptions' => Application::STATUS_LABELS,
        ]);
    }

    public function show(Application $application): View
    {
        $this->ensureBelongsTo($application);

        $application->load([
            'resumeSnapshot', 'jobPosting.workplace', 'jobSeeker',
            'statusLogs.companyUser', 'notes.companyUser',
        ]);

        // 応募者情報(履歴書スナップショット)の閲覧は監査ログの記録対象(SPEC.md 5.3)。
        AuditLog::record('company', auth('company')->id(), 'applications.view', $application, request()->ip());

        return view('company.applications.show', [
            'application' => $application,
            'statusOptions' => Application::STATUS_LABELS,
        ]);
    }

    public function updateStatus(
        ApplicationStatusRequest $request,
        Application $application,
        ChangeApplicationStatusService $service
    ): RedirectResponse {
        $this->ensureBelongsTo($application);

        $service->change($application, $request->string('status')->toString(), auth('company')->user());

        return redirect()->route('company.applications.show', $application)->with('status', '選考ステータスを更新しました。');
    }

    public function storeNote(ApplicationNoteRequest $request, Application $application): RedirectResponse
    {
        $this->ensureBelongsTo($application);

        ApplicationNote::create([
            'application_id' => $application->id,
            'company_user_id' => auth('company')->id(),
            'body' => $request->string('body')->toString(),
        ]);

        return redirect()->route('company.applications.show', $application)->with('status', 'メモを追加しました。');
    }

    public function destroyNote(Application $application, ApplicationNote $note): RedirectResponse
    {
        $this->ensureBelongsTo($application);

        if ($note->application_id !== $application->id) {
            throw new NotFoundHttpException;
        }

        $note->delete();

        return redirect()->route('company.applications.show', $application)->with('status', 'メモを削除しました。');
    }

    public function exportCsv(Request $request, ApplicationSearchService $search, ApplicationCsvExporter $exporter): Response
    {
        $applications = $search->search($request, $this->company());

        AuditLog::record('company', auth('company')->id(), 'applications.export_csv', ipAddress: $request->ip());

        return response($exporter->export($applications), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="applications.csv"',
        ]);
    }

    private function company(): Company
    {
        return auth('company')->user()->company;
    }

    /**
     * URL を組み替えて他社の応募者を操作されるのを防ぐ。
     * 403 ではなく 404 を返す(存在自体を知らせない。CLAUDE.md 3.8)。
     */
    private function ensureBelongsTo(Application $application): void
    {
        if ($application->company_id !== $this->company()->id) {
            throw new NotFoundHttpException;
        }
    }
}
