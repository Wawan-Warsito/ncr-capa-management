<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\NCR;

class ApprovalRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ncr;

    /**
     * Create a new notification instance.
     */
    public function __construct(NCR $ncr)
    {
        $this->ncr = $ncr;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/ncr/{$this->ncr->id}/approve");

        return (new MailMessage)
                    ->subject("Approval Required: NCR {$this->ncr->ncr_number}")
                    ->line("An NCR requires your approval.")
                    ->line("NCR Number: {$this->ncr->ncr_number}")
                    ->action('Review & Approve', $url)
                    ->line('Please review this NCR at your earliest convenience.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ncr_id' => $this->ncr->id,
            'ncr_number' => $this->ncr->ncr_number,
            'message' => "NCR {$this->ncr->ncr_number} requires approval.",
        ];
    }
}
