<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 応募を受け付けたことを求職者へ通知する(TASKS.md T-15)。
 *
 * キュー経由で送るため最大 1 分遅れて届く。応募自体はキューワーカーが
 * 動いていなくても成功する。
 */
class ApplicationReceivedNotification extends Notification implements ShouldQueue
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
            ->subject("【{$siteName}】応募を受け付けました")
            ->greeting("{$notifiable->name} 様")
            ->line("「{$jobPosting->title}」({$jobPosting->company->name})への応募を受け付けました。")
            ->line('選考状況はマイページからご確認いただけます。')
            ->action('マイページを見る', route('seeker.mypage'))
            ->salutation($siteName);
    }
}
