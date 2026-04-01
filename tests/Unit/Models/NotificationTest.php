<?php

namespace Tests\Unit\Models;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_can_be_created()
    {
        $user = User::factory()->create();
        
        $notification = Notification::create([
            'recipient_user_id' => $user->id,
            'notification_type' => 'NCR_Created',
            'title' => 'New NCR Created',
            'message' => 'Test NCR-001 created',
            'created_at' => now(),
        ]);

        $this->assertDatabaseHas('notifications', [
            'recipient_user_id' => $user->id,
            'notification_type' => 'NCR_Created',
            'title' => 'New NCR Created',
        ]);
    }

    public function test_create_notification_helper_works()
    {
        $user = User::factory()->create();
        
        $notification = Notification::createNotification(
            $user->id,
            'NCR_Created',
            'New NCR Created',
            'Test NCR-001 created',
            'NCR',
            1,
            '/ncr/1',
            'High'
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($user->id, $notification->recipient_user_id);
        $this->assertEquals('NCR_Created', $notification->notification_type);
        $this->assertEquals('New NCR Created', $notification->title);
        $this->assertEquals('Test NCR-001 created', $notification->message);
        $this->assertEquals('NCR', $notification->related_entity_type);
        $this->assertEquals(1, $notification->related_entity_id);
        $this->assertEquals('/ncr/1', $notification->action_url);
        $this->assertEquals('High', $notification->priority);
    }

    public function test_notify_users_helper_works()
    {
        $users = User::factory()->count(3)->create();
        $userIds = $users->pluck('id')->toArray();
        
        Notification::notifyUsers(
            $userIds,
            'NCR_Created',
            'New NCR Created',
            'Test NCR-001 created'
        );

        $this->assertEquals(3, Notification::count());
        $this->assertDatabaseHas('notifications', [
            'recipient_user_id' => $userIds[0],
            'notification_type' => 'NCR_Created',
        ]);
    }

    public function test_mark_as_read_works()
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'recipient_user_id' => $user->id,
            'is_read' => false,
            'read_at' => null,
        ]);

        $notification->markAsRead();

        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_as_unread_works()
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'recipient_user_id' => $user->id,
            'is_read' => true,
            'read_at' => now(),
        ]);

        $notification->markAsUnread();

        $this->assertFalse($notification->is_read);
        $this->assertNull($notification->read_at);
    }

    public function test_get_type_icon_helper()
    {
        $notification = new Notification(['notification_type' => 'NCR_Created']);
        $this->assertEquals('document-plus', $notification->type_icon);

        $notification = new Notification(['notification_type' => 'Approval_Required']);
        $this->assertEquals('clipboard-check', $notification->type_icon);

        $notification = new Notification(['notification_type' => 'Unknown']);
        $this->assertEquals('bell', $notification->type_icon);
    }
}
