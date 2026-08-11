<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
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
        /*
         * メール認証(T-27)。標準の VerifyEmail 通知はルート名 `verification.verify`
         * を固定で使うため、求職者専用のルート名(`seeker.`接頭辞)に差し替える。
         * 有効期限はパスワード再設定(60分)より長く、24時間にしている
         * (受信直後に開くとは限らないため)。
         */
        VerifyEmail::createUrlUsing(function (object $notifiable): string {
            return URL::temporarySignedRoute(
                'seeker.verification.verify',
                now()->addHours(24),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            $siteName = SiteSetting::current()->site_name;

            return (new MailMessage)
                ->subject("【{$siteName}】メールアドレスの確認")
                ->greeting('会員登録ありがとうございます')
                ->line('下のボタンからメールアドレスの確認を完了してください。')
                ->action('メールアドレスを確認する', $url)
                ->line('このリンクは 24 時間で無効になります。')
                ->line('心当たりがない場合は、このメールを破棄してください。')
                ->salutation($siteName);
        });
    }
}
