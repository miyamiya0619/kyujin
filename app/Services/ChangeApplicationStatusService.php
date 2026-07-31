<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationStatusLog;
use App\Models\CompanyUser;
use Illuminate\Support\Facades\DB;

/**
 * 応募の選考ステータスを変更する(SPEC.md 8.3)。
 *
 * 変更のたびに `application_status_logs` へ追記し、誰がいつ何から何に変えたかを
 * 証跡として残す。不採用・辞退になっても応募レコード自体は削除しない
 * (一覧に残り続ける。TASKS.md T-13 の完了条件)。
 */
class ChangeApplicationStatusService
{
    public function change(Application $application, string $toStatus, CompanyUser $companyUser): Application
    {
        DB::transaction(function () use ($application, $toStatus, $companyUser) {
            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'company_user_id' => $companyUser->id,
                'from_status' => $application->status,
                'to_status' => $toStatus,
            ]);

            $application->update(['status' => $toStatus]);
        });

        return $application->fresh();
    }
}
