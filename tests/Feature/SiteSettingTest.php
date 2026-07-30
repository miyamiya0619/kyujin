<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SiteSetting::current() が DB のデフォルト値を正しく反映することを保証する。
 *
 * `create([])` の直後は Eloquent が DB 側のデフォルト値をモデルに反映しないため、
 * `requires_review` が `null`(falsy)のまま返り、審査 OFF の分岐に誤って
 * 入ってしまう不具合が実際に起きた(T-08)。
 */
class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_行が作られマイグレーションの既定値が反映される(): void
    {
        // アプリケーション起動時に ThemeServiceProvider が SiteSetting::current() を
        // 呼ぶため、この時点で既に 1 行作られている場合がある。件数ではなく、
        // 値が create([]) 直後でも正しく反映されていることを検証する。
        $setting = SiteSetting::current();

        $this->assertDatabaseCount('site_settings', 1);
        $this->assertTrue($setting->requires_review, '審査は既定で ON であること');
        $this->assertTrue($setting->enables_member);
        $this->assertTrue($setting->enables_posting_plan);
    }

    public function test_2回目以降の呼び出しは同じ行を返す(): void
    {
        $first = SiteSetting::current();
        $second = SiteSetting::current();

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('site_settings', 1);
    }
}
