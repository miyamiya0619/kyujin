<?php

namespace App\Support\Theme;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Throwable;

/**
 * テーマ機構。**この製品の生命線。**
 *
 * 顧客ごとにデザインを個別制作しながら、コードベースは 1 本に保つための仕組み。
 * WordPress のテーマに相当する。
 *
 *   resources/views/
 *     core/                   ← 全顧客共通のテンプレート(フォールバック)
 *     themes/default/         ← 標準テーマ
 *     themes/kaigo-kyokai/    ← 顧客A専用テーマ(差分だけ置けばよい)
 *   public/themes/{theme}/    ← テーマの CSS・画像
 *
 * ビューは「テーマ → コア」の順に探索される。
 * テーマに無いファイルはコアが使われるため、**テーマは変えたいファイルだけ置けばよい。**
 *
 * ⚠ テーマに入れてよいのは Blade のマークアップ・CSS・画像だけ。
 *    クエリ・業務ロジック・認可判定を書いてはいけない(CLAUDE.md 3.2)。
 *    テーマが自分でデータを取りに行った瞬間、コアの変更でテーマが壊れるようになり、
 *    全環境への一斉配布ができなくなる。
 */
class ThemeManager
{
    public const DEFAULT_THEME = 'default';

    private ?string $current = null;

    /**
     * 現在のテーマ名。
     */
    public function current(): string
    {
        return $this->current ??= $this->resolveFromSettings();
    }

    /**
     * テーマを切り替えてビューの探索パスを組み直す。
     * 運営者がサイト設定でテーマを変えたときと、テストで使う。
     */
    public function switchTo(string $theme): void
    {
        $this->current = $theme;
        $this->applyViewPaths();
    }

    /**
     * ビューの探索パスを「テーマ → コア」の順に設定する。
     * アプリケーションの起動時に 1 回呼ばれる。
     */
    public function applyViewPaths(): void
    {
        $finder = View::getFinder();

        // 解決済みビューのキャッシュを捨てないと、切り替え前のパスが使われ続ける
        $finder->flush();
        $finder->setPaths($this->viewPaths());
    }

    /**
     * テーマのアセット URL。テーマに無ければ標準テーマにフォールバックする。
     * ビューの解決順と同じ挙動にしてある。
     */
    public function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $relative = "themes/{$this->current()}/{$path}";

        if (! is_file(public_path($relative))) {
            $relative = 'themes/'.self::DEFAULT_THEME."/{$path}";
        }

        return asset($relative);
    }

    /**
     * 導入済みのテーマ一覧。運営者のサイト設定画面で選択肢にする。
     *
     * @return array<int, string>
     */
    public function available(): array
    {
        $root = resource_path('views/themes');

        if (! is_dir($root)) {
            return [self::DEFAULT_THEME];
        }

        $themes = array_map('basename', glob($root.'/*', GLOB_ONLYDIR) ?: []);
        sort($themes);

        return $themes ?: [self::DEFAULT_THEME];
    }

    public function exists(string $theme): bool
    {
        return is_dir($this->themeViewPath($theme));
    }

    public function themeViewPath(string $theme): string
    {
        return resource_path("views/themes/{$theme}");
    }

    /**
     * 探索パス。先にあるものが優先される。
     *
     * @return array<int, string>
     */
    private function viewPaths(): array
    {
        $paths = [];

        $themePath = $this->themeViewPath($this->current());

        if (is_dir($themePath)) {
            $paths[] = $themePath;
        }

        // テーマに無いファイルはコアを使う
        $paths[] = resource_path('views/core');

        // Laravel の既定パス。ベンダー由来のビューのために残す。
        $paths[] = resource_path('views');

        return $paths;
    }

    /**
     * サイト設定からテーマ名を読む。
     *
     * マイグレーション前や DB 未接続でも起動できなければならないため、
     * 失敗したら標準テーマにフォールバックする。
     */
    private function resolveFromSettings(): string
    {
        try {
            $theme = SiteSetting::current()->theme;
        } catch (Throwable) {
            return self::DEFAULT_THEME;
        }

        // 設定されたテーマのディレクトリが無い場合も落とさない。
        // 顧客環境にテーマの配布が届いていない状況でサイトが真っ白になるのを防ぐ。
        return $theme && $this->exists($theme) ? $theme : self::DEFAULT_THEME;
    }
}
