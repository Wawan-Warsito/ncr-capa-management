<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\NCR;
use App\Models\Department;
use App\Models\DefectCategory;
use App\Models\SeverityLevel;
use Laravel\Sanctum\Sanctum;

class NCRControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create Admin Role
        $role = \App\Models\Role::factory()->create(['role_name' => 'Admin']);
        $this->user = User::factory()->create(['role_id' => $role->id]);
    }

    public function test_index_returns_camel_case_data()
    {
        $ncr = NCR::factory()->create();
        
        $response = $this->actingAs($this->user)->getJson('/api/ncrs');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'ncrNumber', // camelCase
                             'orderNumber', // camelCase
                             'dateFound', // camelCase
                             'status'
                         ]
                     ],
                     'success'
                 ]);
        
        // Ensure snake_case is NOT present
        $data = $response->json('data.0');
        $this->assertArrayHasKey('ncrNumber', $data);
        $this->assertArrayNotHasKey('ncr_number', $data);
    }

    public function test_show_returns_camel_case_data()
    {
        $ncr = NCR::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/ncrs/{$ncr->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'ncrNumber',
                         'orderNumber',
                         'status'
                     ],
                     'success'
                 ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('ncrNumber', $data);
        $this->assertArrayNotHasKey('ncr_number', $data);
    }

    public function test_store_returns_camel_case_data()
    {
        $finderDept = Department::factory()->create(['department_code' => 'QC']);
        $receiverDept = Department::factory()->create();
        $defectCategory = DefectCategory::factory()->create();
        $severityLevel = SeverityLevel::factory()->create();

        $data = [
            'finder_dept_id' => $finderDept->id,
            'receiver_dept_id' => $receiverDept->id,
            'defect_category_id' => $defectCategory->id,
            'severity_level_id' => $severityLevel->id,
            'defect_description' => 'Test NCR Description',
            'order_number' => 'ORD-123',
            'drawing_number' => 'DWG-456',
            'date_found' => now()->toDateString(),
            'quantity_suspect' => 10,
            'quantity_rejected' => 5,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ncrs', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'ncrNumber',
                         'orderNumber',
                         'status'
                     ],
                     'success',
                     'message'
                 ]);

        $responseData = $response->json('data');
        $this->assertArrayHasKey('ncrNumber', $responseData);
        $this->assertArrayNotHasKey('ncr_number', $responseData);
    }
}
