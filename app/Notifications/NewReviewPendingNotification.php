<?php

namespace App\Notifications;

use App\Models\JobPosting;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * 新しい求人が審査待ちになったことを運営者へ通知する(TASKS.md T-15)。
 *
 * 審査待ちが滞留すると掲載企業の公開が遅れ続けるため、運営者の日常業務
 * (SPEC.md 7章)を後押しする通知。キュー経由で送るため最大 1 分遅れて届く。
 */
class NewReviewPendingNotification extends Notification implements ShouldQueue
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
            ->subject("【{$siteName}】新しい審査待ちの求人があります")
            ->greeting("{$notifiable->name} 様")
            ->line("「{$this->jobPosting->company->name}」から求人「{$this->jobPosting->title}」が審査に提出されました。")
            ->action('審査待ち一覧を確認する', route('admin.reviews.index'))
            ->salutation($siteName);
    }
}
