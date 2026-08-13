<?php

namespace App\Notifications;

use App\Models\SiteSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 求職者の退会が完了したことを通知する。
 *
 * **意図的にキューに載せていない。** 送信時点でアカウント(job_seekers 行)は
 * 既に削除済みのため、キュー経由にすると再実行時にモデルを再取得できず失敗する
 * (ResetPasswordNotification と同じ理由でキューを使わない訳とは別に、
 * このケースはキューに載せられない)。
 */
class AccountDeletedNotification extends Notification
{
    public function __construct(
        private readonly string $name,
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
        $siteName = SiteSetting::current()->site_name;

        return (new MailMessage)
            ->subject("【{$siteName}】退会手続きが完了しました")
            ->greeting("{$this->name} 様")
            ->line('退会の手続きが完了しました。ご利用ありがとうございました。')
            ->line('プロフィール・保有資格・職務経歴のデータは削除されています。')
            ->line('心当たりがない場合は、このメールにお心当たりのない旨をご連絡ください。')
            ->salutation($siteName);
    }
}
