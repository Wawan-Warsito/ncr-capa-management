<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $entity;
    protected $type;
    protected $dueDate;

    /**
     * Create a new notification instance.
     * 
     * @param mixed $entity The model instance (NCR or CAPA)
     * @param string $type 'NCR' or 'CAPA'
     */
    public function __construct($entity, string $type)
    {
        $this->entity = $entity;
        $this->type = $type;
        $this->dueDate = $type === 'NCR' ? $entity->response_limit_date : $entity->target_completion_date;
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
        $number = $this->type === 'NCR' ? $this->entity->ncr_number : $this->entity->capa_number;
        $url = url("/" . strtolower($this->type) . "/{$this->entity->id}");
        $dateStr = $this->dueDate ? $this->dueDate->format('Y-m-d') : 'Unknown';

        return (new MailMessage)
                    ->subject("OVERDUE ALERT: {$this->type} {$number}")
                    ->greeting("Urgent Attention Required")
                    ->line("The following {$this->type} is now OVERDUE.")
                    ->line("{$this->type} Number: {$number}")
                    ->line("Due Date: {$dateStr}")
                    ->action("View {$this->type}", $url)
                    ->line('Please take immediate action.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $number = $this->type === 'NCR' ? $this->entity->ncr_number : $this->entity->capa_number;
        
        return [
            'entity_type' => $this->type,
            'entity_id' => $this->entity->id,
            'number' => $number,
            'message' => "Overdue alert for {$this->type} {$number}.",
        ];
    }
}
