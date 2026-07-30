<?php

namespace App\Notifications;

use App\Models\SiteSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 掲載企業の担当者への招待メール。
 *
 * **パスワードをメールで送らない。** 受け取った本人に設定させる。
 * メールは平文で保管・転送されるため、パスワードを本文に書くと
 * 受信箱に残り続ける。パスワード再設定と同じトークンの仕組みを使う。
 *
 * 招待は運営者の操作で送られるため即時性が求められる。
 * T-15 でキュー運用を整えるまでは同期送信とする。
 */
class CompanyUserInvitationNotification extends Notification
{
    public function __construct(
        private readonly string $token,
        private readonly string $companyName,
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
        $url = route('company.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $siteName = SiteSetting::current()->site_name;

        return (new MailMessage)
            ->subject("【{$siteName}】掲載企業アカウントのご案内")
            ->greeting("{$notifiable->name} 様")
            ->line("{$siteName} に「{$this->companyName}」の担当者アカウントを作成しました。")
            ->line('下のボタンからパスワードを設定すると、求人の登録や応募者の確認ができるようになります。')
            ->action('パスワードを設定する', $url)
            ->line('このリンクは 60 分で無効になります。期限が切れた場合はログイン画面の「パスワードを忘れた方」からお手続きください。')
            ->salutation($siteName);
    }
}
