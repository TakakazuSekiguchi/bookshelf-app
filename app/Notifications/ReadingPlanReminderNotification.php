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
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $messages = [
            'three_days_before' => [
                'title' => '読書計画の期日が近づいています',
                'body' => "{$this->readingPlan->book->title} の期日は3日後です。",
            ],
            'on_due_date' => [
                'title' => '読書計画の期日です',
                'body' => "{$this->readingPlan->book->title} の期日は本日です。",
            ],
            'three_days_after' => [
                'title' => '読書計画の期日を過ぎています',
                'body' => "{$this->readingPlan->book->title} の期日から3日が経過しました。",
            ],
        ];

        return [
            'timing' => $this->timing,
            'title' => $messages[$this->timing]['title'],
            'body' => $messages[$this->timing]['body'],
        ];
    }
}
