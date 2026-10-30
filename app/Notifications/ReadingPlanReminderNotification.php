<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ReadingPlan $readingPlan,
        public string $timing,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $messages = [
            'three_days_before' => [
                'title' => '読書計画リマインド — 期限まであと 3 日',
                'body' => "「{$this->readingPlan->book->title}」の期限まで残り 3 日です。引き続き読書を進めましょう。",
            ],
            'on_due_date' => [
                'title' => '読書計画 — 本日が期限',
                'body' => "「{$this->readingPlan->book->title}」は本日が期限です。読了済みなら完了登録を、もう少し必要なら期限を変更してください。",
            ],
            'three_days_after' => [
                'title' => '読書計画 — 期限超過 3 日経過',
                'body' => "「{$this->readingPlan->book->title}」の期限から 3 日が経過しました。読了済みなら完了登録、続けるなら期限を変更してください。",
            ],
        ];

        return [
            'timing' => $this->timing,
            'title' => $messages[$this->timing]['title'],
            'body' => $messages[$this->timing]['body'],
        ];
    }
}
