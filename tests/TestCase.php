<?php

namespace Tests;

use App\Models\SiteSetting;
use App\Support\Theme\ThemeManager;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // SiteSetting はリクエスト内メモ化を静的プロパティで持つ。
        // RefreshDatabase は DB を巻き戻すが静的プロパティは残るため、
        // 明示的に捨てないと前のテストの設定が次のテストに漏れる。
        SiteSetting::forgetMemo();

        // テーマも同様に、前のテストで切り替えた状態を持ち越さない。
        $this->app->forgetInstance(ThemeManager::class);
        $this->app->make(ThemeManager::class)->applyViewPaths();
    }
}
