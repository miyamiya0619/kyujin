<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\Theme\ThemeManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * テーマ機構のテスト。
 *
 * この仕組みが壊れると「コードは 1 本」と「デザインは個別制作」の両立が崩れ、
 * 全環境への一斉配布ができなくなる(SPEC.md 9章)。
 *
 * **実際のトップページ(public.top)には依存しない。** トップページの中身が
 * 変わるたびにこのテストが壊れるのを避けるため、テスト専用の使い捨てビューと
 * ルートを動的に作り、それに対してテーマの解決順序だけを検証する。
 */
class ThemeTest extends TestCase
{
    use RefreshDatabase;

    /** テスト中だけ作る仮のテーマ。 */
    private const THEME = 'test-theme';

    /** コアの本物のビューを汚さないよう、テスト専用の使い捨てビュー名を使う。 */
    private const PROBE_VIEW = 'public.__theme_probe';

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeCoreView(self::PROBE_VIEW, <<<'BLADE'
            @extends('layouts.app')
            @section('content')<p>コア側のプローブページ</p>@endsection
            BLADE);

        Route::get('/__theme-probe', fn () => view(self::PROBE_VIEW))->middleware('web');
    }

    protected function tearDown(): void
    {
        File::delete($this->corePath(self::PROBE_VIEW));
        File::deleteDirectory(resource_path('views/themes/'.self::THEME));
        File::deleteDirectory(public_path('themes/'.self::THEME));

        parent::tearDown();
    }

    public function test_テーマに何も置かなければコアのテンプレートが使われる(): void
    {
        $this->get('/__theme-probe')
            ->assertOk()
            ->assertSee('コア側のプローブページ');
    }

    public function test_テーマに同名のファイルを置くとそちらが使われる(): void
    {
        $this->makeThemeView(self::PROBE_VIEW, <<<'BLADE'
            @extends('layouts.app')
            @section('content')<p>テーマ側のプローブページ</p>@endsection
            BLADE);

        $this->useTheme(self::THEME);

        $this->get('/__theme-probe')
            ->assertOk()
            ->assertSee('テーマ側のプローブページ')
            ->assertDontSee('コア側のプローブページ');
    }

    public function test_テーマに無いファイルはコアにフォールバックする(): void
    {
        // プローブページだけ差し替える。レイアウト・ヘッダー・フッターはコアのまま。
        $this->makeThemeView(self::PROBE_VIEW, <<<'BLADE'
            @extends('layouts.app')
            @section('content')<p>テーマ側のプローブページ</p>@endsection
            BLADE);

        $this->useTheme(self::THEME);

        $response = $this->get('/__theme-probe')->assertOk();

        // テーマのプローブが使われている
        $response->assertSee('テーマ側のプローブページ');
        // レイアウトとヘッダーはコアが使われている(サイト名はコアのヘッダーが出力する)
        $response->assertSee(SiteSetting::current()->site_name);
    }

    public function test_テーマを切り替えても全ページが表示される(): void
    {
        $this->get('/__theme-probe')->assertOk();

        $this->makeThemeView(self::PROBE_VIEW, <<<'BLADE'
            @extends('layouts.app')
            @section('content')<p>テーマ側のプローブページ</p>@endsection
            BLADE);

        $this->useTheme(self::THEME);
        $this->get('/__theme-probe')->assertOk();

        // 標準テーマに戻しても壊れない
        $this->useTheme(ThemeManager::DEFAULT_THEME);
        $this->get('/__theme-probe')
            ->assertOk()
            ->assertSee('コア側のプローブページ');
    }

    public function test_存在しないテーマが設定されていても標準テーマで表示される(): void
    {
        // 顧客環境にテーマの配布が届いていない状況。
        // ここで落ちるとサイトが真っ白になるため、必ずフォールバックさせる。
        $this->useTheme('not-distributed-yet');

        $this->get('/__theme-probe')->assertOk();

        $this->assertSame(ThemeManager::DEFAULT_THEME, theme()->current());
    }

    public function test_テーマのアセット_ur_lが返る(): void
    {
        // テーマの「導入済み」判定はビューディレクトリの有無で行う。
        // アセットだけ置いても未導入扱いになるため、ビューも用意する。
        $this->makeThemeView(self::PROBE_VIEW, '@extends("layouts.app")');
        $this->makeThemeAsset('css/theme.css', 'body{}');
        $this->useTheme(self::THEME);

        $this->assertStringContainsString(
            'themes/'.self::THEME.'/css/theme.css',
            theme_asset('css/theme.css')
        );
    }

    public function test_テーマにアセットが無ければ標準テーマにフォールバックする(): void
    {
        // テーマのビューだけ用意し、CSS は置かない
        $this->makeThemeView(self::PROBE_VIEW, '@extends("layouts.app")');
        $this->useTheme(self::THEME);

        $this->assertStringContainsString(
            'themes/'.ThemeManager::DEFAULT_THEME.'/css/theme.css',
            theme_asset('css/theme.css'),
            'テーマに無いアセットは標準テーマのものを返すこと'
        );
    }

    public function test_導入済みテーマを列挙できる(): void
    {
        $this->makeThemeView(self::PROBE_VIEW, '@extends("layouts.app")');

        $available = theme()->available();

        $this->assertContains(ThemeManager::DEFAULT_THEME, $available);
        $this->assertContains(self::THEME, $available);
    }

    /**
     * サイト設定を書き換えてテーマを適用する。
     * 本番と同じ経路(設定 → テーマ解決)を通す。
     */
    private function useTheme(string $theme): void
    {
        SiteSetting::current()->update(['theme' => $theme]);

        $this->app->forgetInstance(ThemeManager::class);
        $this->app->make(ThemeManager::class)->applyViewPaths();
    }

    private function makeCoreView(string $dotName, string $contents): void
    {
        $path = $this->corePath($dotName);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);
    }

    private function corePath(string $dotName): string
    {
        return resource_path('views/core/'.str_replace('.', '/', $dotName).'.blade.php');
    }

    private function makeThemeView(string $dotName, string $contents): void
    {
        $path = resource_path('views/themes/'.self::THEME.'/'.str_replace('.', '/', $dotName).'.blade.php');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);
    }

    private function makeThemeAsset(string $relativePath, string $contents): void
    {
        $path = public_path('themes/'.self::THEME.'/'.$relativePath);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);
    }
}
