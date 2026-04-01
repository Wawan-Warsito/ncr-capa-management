<?php

namespace Tests\Feature\Api;

use App\Models\CAPA;
use App\Models\NCR;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CAPAControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $ncr;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role
        $adminRole = \App\Models\Role::factory()->create(['role_name' => 'Admin']);

        // Create admin user
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        
        // Create an NCR
        $this->ncr = NCR::factory()->create();
    }

    public function test_index_returns_capa_list()
    {
        CAPA::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
                         ->getJson('/api/capas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'capaNumber',
                             'ncrId',
                             'currentStatus',
                             // ... check a few fields
                         ]
                     ],
                     'success'
                 ]);
    }

    public function test_show_returns_capa_detail()
    {
        $capa = CAPA::factory()->create();

        $response = $this->actingAs($this->admin)
                         ->getJson("/api/capas/{$capa->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $capa->id,
                         'capaNumber' => $capa->capa_number,
                     ]
                 ]);
    }

    public function test_store_creates_new_capa()
    {
        $pic = User::factory()->create();

        $data = [
            'ncr_id' => $this->ncr->id,
            'rca_method' => '5_Why',
            'root_cause_summary' => 'Test Root Cause',
            'corrective_action_plan' => 'Fix it',
            'assigned_pic_id' => $pic->id,
            'target_completion_date' => now()->addDays(7)->toDateString(),
        ];

        $response = $this->actingAs($this->admin)
                         ->postJson('/api/capas', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => ['id', 'capaNumber'],
                     'success',
                     'message'
                 ]);

        $this->assertDatabaseHas('capas', [
            'ncr_id' => $this->ncr->id,
            'root_cause_summary' => 'Test Root Cause',
        ]);
    }

    public function test_update_modifies_capa()
    {
        $capa = CAPA::factory()->create();

        $data = [
            'root_cause_summary' => 'Updated Root Cause',
        ];

        $response = $this->actingAs($this->admin)
                         ->putJson("/api/capas/{$capa->id}", $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'CAPA updated successfully',
                 ]);

        $this->assertDatabaseHas('capas', [
            'id' => $capa->id,
            'root_cause_summary' => 'Updated Root Cause',
        ]);
    }

    public function test_update_progress()
    {
        $capa = CAPA::factory()->create([
            'assigned_pic_id' => $this->admin->id,
            'current_status' => 'In_Progress'
        ]);

        $data = [
            'progress_percentage' => 50,
            'milestone_description' => 'Halfway there',
        ];

        $response = $this->actingAs($this->admin)
                         ->putJson("/api/capas/{$capa->id}/progress", $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Progress updated successfully',
                 ]);

        $this->assertDatabaseHas('capas', [
            'id' => $capa->id,
            'progress_percentage' => 50,
        ]);
    }
}
