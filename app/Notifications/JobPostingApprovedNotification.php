<?php

namespace App\Notifications;

use App\Models\JobPosting;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 求人が承認され公開されたことを掲載企業へ通知する(TASKS.md T-15)。
 *
 * キュー経由で送る。cron から毎分 `queue:work --stop-when-empty` を実行する
 * 運用のため、最大 1 分遅れて届く。
 */
class JobPostingApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly JobPosting $jobPosting) {}

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
            ->subject("【{$siteName}】求人「{$this->jobPosting->title}」が公開されました")
            ->greeting("{$notifiable->name} 様")
            ->line("求人「{$this->jobPosting->title}」の審査が完了し、公開されました。")
            ->action('求人を確認する', route('public.jobs.show', $this->jobPosting))
            ->salutation($siteName);
    }
}
