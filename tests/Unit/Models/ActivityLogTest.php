<?php

namespace Tests\Unit\Models;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_can_be_created()
    {
        $user = User::factory()->create();
        
        $log = ActivityLog::create([
            'user_id' => $user->id,
            'entity_type' => 'NCR',
            'entity_id' => 1,
            'action_type' => 'Created',
            'action_description' => 'Test NCR Created',
            'performed_at' => now(),
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'entity_type' => 'NCR',
            'action_type' => 'Created',
        ]);
    }

    public function test_log_activity_helper_works()
    {
        $user = User::factory()->create();
        
        $log = ActivityLog::logActivity(
            'NCR',
            1,
            'Created',
            'Test NCR Created',
            null,
            ['name' => 'New NCR'],
            $user
        );

        $this->assertInstanceOf(ActivityLog::class, $log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('NCR', $log->entity_type);
        $this->assertEquals(1, $log->entity_id);
        $this->assertEquals('Created', $log->action_type);
        $this->assertEquals('Test NCR Created', $log->action_description);
        $this->assertNotNull($log->new_values);
    }

    public function test_log_activity_helper_uses_authenticated_user()
    {
        $user = User::factory()->create();
        Auth::login($user);
        
        $log = ActivityLog::logActivity(
            'NCR',
            1,
            'Updated',
            'Test NCR Updated'
        );

        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_get_changes_summary_attribute()
    {
        $log = new ActivityLog([
            'old_values' => ['status' => 'Draft', 'priority' => 'Low'],
            'new_values' => ['status' => 'Open', 'priority' => 'High'],
        ]);

        $summary = $log->changes_summary;

        $this->assertCount(2, $summary);
        $this->assertEquals('status', $summary[0]['field']);
        $this->assertEquals('Draft', $summary[0]['old']);
        $this->assertEquals('Open', $summary[0]['new']);
        $this->assertEquals('priority', $summary[1]['field']);
        $this->assertEquals('Low', $summary[1]['old']);
        $this->assertEquals('High', $summary[1]['new']);
    }

    public function test_get_status_color_helper()
    {
        $this->assertEquals('blue', ActivityLog::getStatusColor('Draft'));
        $this->assertEquals('orange', ActivityLog::getStatusColor('Pending'));
        $this->assertEquals('green', ActivityLog::getStatusColor('Verified'));
        $this->assertEquals('gray', ActivityLog::getStatusColor('Unknown'));
    }
}
