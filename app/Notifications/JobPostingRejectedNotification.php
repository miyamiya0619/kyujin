<?php

namespace App\Notifications;

use App\Models\JobPosting;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 求人が差戻しされたことを掲載企業へ通知する。
 *
 * キュー経由で送る(T-15 で本格的なキュー運用を整える)。
 * 本番は cron 経由のため最大 1 分遅れるが、差戻しは即時性より確実な到達を優先する。
 */
class JobPostingRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly JobPosting $jobPosting,
        private readonly string $reason,
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
            ->subject("【{$siteName}】求人「{$this->jobPosting->title}」の差戻しについて")
            ->greeting("{$notifiable->name} 様")
            ->line("求人「{$this->jobPosting->title}」を審査した結果、修正が必要と判断されました。")
            ->line('差戻し理由:')
            ->line($this->reason)
            ->line('内容を修正のうえ、再度提出してください。')
            ->action('求人を編集する', route('company.job-postings.edit', $this->jobPosting))
            ->salutation($siteName);
    }
}
