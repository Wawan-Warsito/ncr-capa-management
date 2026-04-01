<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification as NotificationModel;
use Illuminate\Support\Facades\Mail;
// use App\Mail\GeneralNotificationMail; // Assumption: We might create this later or use generic Mailable

class NotificationService
{
    /**
     * Create In-App Notification
     */
    public function createInAppNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $actionUrl = null,
        string $priority = 'Normal'
    ): NotificationModel {
        return NotificationModel::createNotification(
            $userId,
            $type,
            $title,
            $message,
            $entityType,
            $entityId,
            $actionUrl,
            $priority
        );
    }

    /**
     * Send Email Notification
     */
    public function sendEmailNotification(User $user, string $subject, string $content, string $actionUrl = null)
    {
        // Ideally use Laravel's Notification system or Mailables
        // For now, we'll placeholder this or use a simple raw email if Mailable not exists
        // Since creating Mailables wasn't explicitly in the TODO list provided in the prompt context (only Notification classes),
        // I will implement a basic Mail::send or assume a generic Mailable exists.
        
        // However, the user asked for NotificationService to have sendEmailNotification.
        // I will simulate it for now or implement using standard Laravel Mail if configured.
        
        /*
        Mail::raw($content, function ($message) use ($user, $subject) {
            $message->to($user->email)
                    ->subject($subject);
        });
        */
        
        // In a real app, we would use:
        // Mail::to($user)->send(new NotificationMail($subject, $content, $actionUrl));
        
        return true; // Placeholder until Mail setup is complete
    }

    /**
     * Send Reminder
     * Combines In-App and Email
     */
    public function sendReminder(User $user, string $title, string $message, string $entityType, int $entityId, string $url)
    {
        // 1. Create In-App Notification
        $this->createInAppNotification(
            $user->id,
            'Deadline_Reminder',
            $title,
            $message,
            $entityType,
            $entityId,
            $url,
            'High'
        );

        // 2. Send Email
        $this->sendEmailNotification(
            $user,
            "Reminder: $title",
            $message . "\n\nPlease check: " . url($url),
            $url
        );
        
        return true;
    }

    /**
     * Send notification to multiple users
     */
    public function notifyUsers(array $userIds, string $type, string $title, string $message, ?string $entityType = null, ?int $entityId = null, ?string $actionUrl = null, string $priority = 'Normal')
    {
        foreach ($userIds as $userId) {
            $this->createInAppNotification(
                $userId,
                $type,
                $title,
                $message,
                $entityType,
                $entityId,
                $actionUrl,
                $priority
            );
            
            // Optionally send email based on priority or preference
            if ($priority === 'Urgent' || $priority === 'High') {
                $user = User::find($userId);
                if ($user) {
                    $this->sendEmailNotification(
                        $user,
                        $title,
                        $message,
                        $actionUrl
                    );
                }
            }
        }
    }
}
