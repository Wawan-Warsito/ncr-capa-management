<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\NCR;

class NCRCreatedNotification extends Notification implements ShouldQueue
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
        // We can add 'database' here if we use Laravel's default database notifications table
        // But we have a custom Notification model.
        // So we might just use 'mail' here, and handle database notification manually in Service/Controller
        // OR we can implement a custom channel.
        // For simplicity and per instructions, I'll stick to Mail here, 
        // assuming the in-app notification is handled by our Service logic or we add a 'database' channel that maps to our table.
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/ncr/{$this->ncr->id}");

        return (new MailMessage)
                    ->subject("New NCR Created: {$this->ncr->ncr_number}")
                    ->line("A new NCR has been created.")
                    ->line("NCR Number: {$this->ncr->ncr_number}")
                    ->line("Defect: {$this->ncr->defect_description}")
                    ->action('View NCR', $url)
                    ->line('Thank you for using our application!');
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
            'message' => "New NCR {$this->ncr->ncr_number} created.",
        ];
    }
}
