<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'recipient_user_id' => User::factory(),
            'notification_type' => 'NCR_Created',
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->sentence(8),
            'related_entity_type' => null,
            'related_entity_id' => null,
            'action_url' => null,
            'priority' => 'Normal',
            'is_read' => false,
            'read_at' => null,
            'is_email_sent' => false,
            'email_sent_at' => null,
            'created_at' => now(),
        ];
    }
}

