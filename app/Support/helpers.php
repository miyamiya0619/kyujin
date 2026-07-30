<?php

use App\Support\Theme\ThemeManager;

if (! function_exists('theme')) {
    /**
     * テーマ機構への入り口。
     * テーマ名の取得・切り替え・導入済みテーマの一覧に使う。
     */
    function theme(): ThemeManager
    {
        return app(ThemeManager::class);
    }
}

if (! function_exists('theme_asset')) {
    /**
     * テーマの CSS・画像の URL を返す。
     * テーマに無ければ標準テーマにフォールバックする(ビューの解決順と同じ挙動)。
     *
     *   <link rel="stylesheet" href="{{ theme_asset('css/theme.css') }}">
     *   <img src="{{ theme_asset('img/hero.jpg') }}" alt="">
     */
    function theme_asset(string $path): string
    {
        return app(ThemeManager::class)->asset($path);
    }
}
