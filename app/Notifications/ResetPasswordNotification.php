<?php

namespace App\Notifications;

use App\Models\SiteSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * パスワード再設定メール。
 *
 * **意図的にキューに載せていない。**
 * 本番のキューは cron から毎分実行するため、キューに載せると最大 1 分遅れる。
 * パスワード再設定は待たされると体験が悪く、その場で届くことのほうが重要。
 * 他の通知(応募・審査など)は T-15 でキュー経由にする。
 */
class ResetPasswordNotification extends Notification
{
    public function __construct(
        private readonly string $token,
        private readonly string $resetRouteName,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route($this->resetRouteName, [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $siteName = SiteSetting::current()->site_name;

        return (new MailMessage)
            ->subject("【{$siteName}】パスワード再設定のご案内")
            ->greeting('パスワード再設定のご案内')
            ->line('パスワード再設定のリクエストを受け付けました。下のボタンから新しいパスワードを設定してください。')
            ->action('パスワードを再設定する', $url)
            ->line('このリンクは 60 分で無効になります。')
            ->line('心当たりがない場合は、このメールを破棄してください。パスワードは変更されません。')
            ->salutation($siteName);
    }
}
