<?php

namespace Tests\Feature\Seeker;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * 要配慮個人情報を取得しない設計になっていることの最終確認(CLAUDE.md 3.4)。
 * T-04 で job_seekers に対して同様の検証をしているが、T-11 で追加した
 * job_seeker_experiences にも同じ確認をする。
 */
class SensitiveDataTest extends TestCase
{
    public function test_job_seekersに要配慮個人情報のカラムが存在しない(): void
    {
        $columns = Schema::getColumnListing('job_seekers');

        foreach (['health_condition', 'medical_history', 'disability', 'nationality', 'religion'] as $forbidden) {
            $this->assertNotContains($forbidden, $columns, "禁止カラム「{$forbidden}」が存在してはいけない");
        }
    }

    public function test_job_seeker_experiencesに要配慮個人情報のカラムが存在しない(): void
    {
        $columns = Schema::getColumnListing('job_seeker_experiences');

        foreach (['health_condition', 'medical_history', 'disability', 'nationality', 'religion'] as $forbidden) {
            $this->assertNotContains($forbidden, $columns, "禁止カラム「{$forbidden}」が存在してはいけない");
        }
    }
}
