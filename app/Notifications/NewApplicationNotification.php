<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 新しい応募があったことを掲載企業へ通知する(TASKS.md T-15)。
 *
 * キュー経由で送るため最大 1 分遅れて届く。応募・審査自体はキューワーカーが
 * 動いていなくても成功する(通知の送信失敗と業務処理を分離するため)。
 */
class NewApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Application $application) {}

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
        $jobPosting = $this->application->jobPosting;

        return (new MailMessage)
            ->subject("【{$siteName}】求人「{$jobPosting->title}」に応募がありました")
            ->greeting("{$notifiable->name} 様")
            ->line("求人「{$jobPosting->title}」に新しい応募がありました。")
            ->line('応募内容は応募者一覧からご確認いただけます。')
            ->action('応募者を確認する', route('company.applications.show', $this->application))
            ->salutation($siteName);
    }
}
