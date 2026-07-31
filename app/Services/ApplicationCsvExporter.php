<?php

namespace App\Services;

use App\Models\Application;

/**
 * 応募者一覧の CSV 出力(TASKS.md T-13)。
 *
 * 出力項目は氏名・連絡先・応募日・ステータスのみ(SPEC.md 5.2)。
 *
 * ⚠ 氏名・連絡先は必ず応募時点のスナップショットから読む。
 *   `job_seekers` の現在値を直接参照してはいけない(CLAUDE.md 3.6)。
 */
class ApplicationCsvExporter
{
    /**
     * @param  iterable<int, Application>  $applications
     */
    public function export(iterable $applications): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['氏名', 'メールアドレス', '電話番号', '応募日', 'ステータス']);

        foreach ($applications as $application) {
            $snapshot = $application->resumeSnapshot?->payload ?? [];

            fputcsv($handle, [
                $snapshot['name'] ?? '',
                $snapshot['email'] ?? '',
                $snapshot['tel'] ?? '',
                $application->applied_at->format('Y-m-d'),
                $application->statusLabel(),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        // Excel(日本語環境)で文字化けしないよう UTF-8 の BOM を付ける。
        return "\xEF\xBB\xBF".$csv;
    }
}
