<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 選考ステータスが変わったことを求職者へ通知する(TASKS.md T-15)。
 *
 * キュー経由で送るため最大 1 分遅れて届く。ステータス変更自体はキュー
 * ワーカーが動いていなくても成功する。
 */
class ApplicationStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
        private readonly string $toStatus,
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
        $jobPosting = $this->application->jobPosting;
        $label = Application::STATUS_LABELS[$this->toStatus] ?? $this->toStatus;

        return (new MailMessage)
            ->subject("【{$siteName}】選考状況が更新されました")
            ->greeting("{$notifiable->name} 様")
            ->line("「{$jobPosting->title}」({$jobPosting->company->name})の選考状況が「{$label}」に更新されました。")
            ->action('マイページで確認する', route('seeker.mypage'))
            ->salutation($siteName);
    }
}
