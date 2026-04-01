<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\NCR;
use App\Models\CAPA;
use App\Models\DefectCategory;
use App\Models\SeverityLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CAPATest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $qcManager;
    protected $pic;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create([
            'role_name' => 'Administrator',
            'display_name' => 'Administrator',
            'permissions' => ['*'],
            'level' => 10,
        ]);

        $qcManagerRole = Role::create([
            'role_name' => 'QC_Manager',
            'display_name' => 'QC Manager',
            'permissions' => ['ncr.view', 'ncr.approve', 'capa.view', 'capa.manage'],
            'level' => 8,
        ]);

        $userRole = Role::create([
            'role_name' => 'User',
            'display_name' => 'User',
            'permissions' => ['ncr.view', 'capa.view'],
            'level' => 1,
        ]);

        // Create departments
        $dept = Department::create([
            'department_code' => 'QC',
            'department_name' => 'Quality Control',
        ]);

        // Create users
        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'department_id' => $dept->id,
        ]);

        $this->qcManager = User::factory()->create([
            'role_id' => $qcManagerRole->id,
            'department_id' => $dept->id,
        ]);

        $this->pic = User::factory()->create([
            'role_id' => $userRole->id,
            'department_id' => $dept->id,
        ]);

        // Create initial data for NCR if needed
        DefectCategory::factory()->create();
        SeverityLevel::factory()->create();
    }

    public function test_can_get_capa_list()
    {
        CAPA::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/capas');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_pic_can_only_see_assigned_capas()
    {
        CAPA::factory()->create(['assigned_pic_id' => $this->pic->id]);
        CAPA::factory()->create(['assigned_pic_id' => $this->admin->id]);

        $response = $this->actingAs($this->pic)
            ->getJson('/api/capas');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_capa()
    {
        $ncr = NCR::factory()->create(['status' => 'Open']);

        $data = [
            'ncr_id' => $ncr->id,
            'assigned_pic_id' => $this->pic->id,
            'target_completion_date' => now()->addDays(30)->toDateString(),
            'priority_level' => 'High',
            'capa_type' => 'Corrective',
            'rca_method' => '5_Why',
            'root_cause_summary' => 'Test root cause',
            'corrective_action_plan' => 'Test corrective action',
        ];

        $response = $this->actingAs($this->qcManager)
            ->postJson('/api/capas', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('capas', [
            'ncr_id' => $ncr->id,
            'assigned_pic_id' => $this->pic->id,
        ]);
    }

    public function test_can_show_capa_detail()
    {
        $capa = CAPA::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/capas/{$capa->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $capa->id,
                ]
            ]);
    }

    public function test_can_update_capa_progress()
    {
        $capa = CAPA::factory()->create([
            'assigned_pic_id' => $this->pic->id,
            'current_status' => 'Planned',
            'progress_percentage' => 0,
        ]);

        $response = $this->actingAs($this->pic)
            ->putJson("/api/capas/{$capa->id}/progress", [
                'progress_percentage' => 50,
                'milestone_description' => 'Halfway done',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('capas', [
            'id' => $capa->id,
            'progress_percentage' => 50,
        ]);
        
        $this->assertDatabaseHas('capa_progress_logs', [
            'capa_id' => $capa->id,
            'progress_percentage' => 50,
            'milestone_description' => 'Halfway done',
        ]);
    }

    public function test_can_verify_capa()
    {
        $capa = CAPA::factory()->create(['current_status' => 'Pending_Verification']);

        $response = $this->actingAs($this->qcManager)
            ->postJson("/api/capas/{$capa->id}/verify", [
                'verification_method' => 'Test Method',
                'verification_results' => 'Test Results',
                'effectiveness_verified' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('capas', [
            'id' => $capa->id,
            'current_status' => 'Verified',
        ]);
    }

    public function test_can_close_capa()
    {
        $capa = CAPA::factory()->create(['current_status' => 'Verified']);

        $response = $this->actingAs($this->qcManager)
            ->postJson("/api/capas/{$capa->id}/close", [
                'closure_notes' => 'Closing CAPA',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('capas', [
            'id' => $capa->id,
            'current_status' => 'Closed',
        ]);
    }
}
