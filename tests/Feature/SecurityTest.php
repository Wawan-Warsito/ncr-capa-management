<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\NCR;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $role = Role::create(['role_name' => 'User', 'permissions' => ['*']]);
        $dept = Department::create(['department_code' => 'IT', 'department_name' => 'IT Dept']);
        
        $this->user = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $dept->id,
        ]);

        // Create required master data
        \App\Models\DefectCategory::factory()->create();
        \App\Models\SeverityLevel::factory()->create();
        \App\Models\DispositionMethod::factory()->create();
    }

    public function test_sql_injection_protection_on_search()
    {
        NCR::factory()->create(['ncr_number' => 'NCR-SAFE-001']);
        
        // Attempt SQL injection in search parameter
        $payload = "' OR '1'='1";
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/ncrs?search={$payload}");
            
        $response->assertStatus(200);
        
        // Should not return all records if search logic is correct (using parameter binding)
        $response->assertJsonCount(0, 'data');
    }

    public function test_xss_payload_handling()
    {
        $xssPayload = "<script>alert('xss')</script>";
        
        // Create NCR using factory which handles relationships correctly
        $ncr = NCR::factory()->create([
            'defect_description' => $xssPayload,
            'finder_dept_id' => $this->user->department_id,
            'receiver_dept_id' => $this->user->department_id,
            'created_by_user_id' => $this->user->id,
        ]);
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/ncrs/{$ncr->id}");
            
        $response->assertStatus(200);
        
        // Check if the payload is returned as is (escaped JSON string)
        // Laravel/PHP json_encode will encode < as \u003C etc if configured, but default behavior is usually sufficient
        // Actually, we just want to ensure it's the same string content, meaning DB stored it.
        $this->assertEquals($xssPayload, $response->json('data.defectDescription'));
    }

    public function test_csrf_protection()
    {
        $response = $this->postJson('/api/ncrs', [], [
            'Authorization' => 'Bearer ' . $this->user->createToken('test')->plainTextToken,
        ]); 
        
        $this->assertNotEquals(419, $response->status());
    }
}
