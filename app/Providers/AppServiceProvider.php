<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         * 画像処理は GD ドライバを使う。
         *
         * Imagick のほうが高機能だが、本番のエックスサーバー(共有レンタル)で
         * 利用できるかは環境依存。GD は確実に使えるため、そちらに揃える。
         * 「ローカルで動くが本番で動かない」を避けるための選択(CLAUDE.md 4章)。
         */
        $this->app->singleton(ImageManager::class, fn () => new ImageManager(new GdDriver));
    }

    public function boot(): void
    {
        //
    }
}
