<?php

namespace App\Notifications;

use App\Models\AdminNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNotificationSent extends Notification implements ShouldQueue
{
    use Queueable;

    // public AdminNotification $adminNotification;

    // /**
    //  * Create a new notification instance.
    //  */
    // public function __construct(AdminNotification $adminNotification)
    // {
    //     $this->adminNotification = $adminNotification;
    // }
    public function __construct(
        public AdminNotification $adminNotification
    ){}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject($this->adminNotification->title)
            ->line($this->adminNotification->content);

        // Attacher les fichiers
        if ($this->adminNotification->attachments) {
            foreach ($this->adminNotification->attachments as $attachment) {
                if (file_exists(storage_path('app/public/' . $attachment))) {
                    $mailMessage->attach(storage_path('app/public/' . $attachment));
                }
            }
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->adminNotification->title,
            'content' => $this->adminNotification->content,
            'attachments' => $this->adminNotification->attachments,
            'sent_by' => $this->adminNotification->sender->name,
        ];
    }
}
