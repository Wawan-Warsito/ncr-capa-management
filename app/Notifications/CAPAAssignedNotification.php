<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\CAPA;

class CAPAAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $capa;

    /**
     * Create a new notification instance.
     */
    public function __construct(CAPA $capa)
    {
        $this->capa = $capa;
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
        $url = url("/capa/{$this->capa->id}");

        return (new MailMessage)
                    ->subject("CAPA Assigned: {$this->capa->capa_number}")
                    ->line("You have been assigned as the PIC for a new CAPA.")
                    ->line("CAPA Number: {$this->capa->capa_number}")
                    ->line("Related NCR: " . ($this->capa->ncr ? $this->capa->ncr->ncr_number : 'N/A'))
                    ->line("Target Completion: " . ($this->capa->target_completion_date ? $this->capa->target_completion_date->format('Y-m-d') : 'N/A'))
                    ->action('View CAPA', $url)
                    ->line('Please take necessary actions.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'capa_id' => $this->capa->id,
            'capa_number' => $this->capa->capa_number,
            'message' => "You have been assigned to CAPA {$this->capa->capa_number}.",
        ];
    }
}
