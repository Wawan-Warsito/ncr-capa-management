<?php

namespace Tests\Feature;

use App\Models\NCR;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\DefectCategory;
use App\Models\SeverityLevel;
use App\Models\DispositionMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $role = Role::create([
            'role_name' => 'Admin',
            'permissions' => ['*'],
        ]);
        
        $dept = Department::create([
            'department_code' => 'IT',
            'department_name' => 'IT Dept',
        ]);
        
        $this->user = User::factory()->create([
            'role_id' => $role->id,
            'department_id' => $dept->id,
        ]);

        DefectCategory::factory()->create();
        SeverityLevel::factory()->create();
        DispositionMethod::factory()->create();
    }

    public function test_ncr_list_performance_and_n_plus_one()
    {
        // Seed database with many records
        NCR::factory()->count(20)->create();
        
        DB::enableQueryLog();
        
        $response = $this->actingAsApi($this->user)
            ->getJson('/api/ncrs');
            
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        
        // With eager loading properly set up, the number of queries should be constant
        // regardless of the number of items (pagination limit is 15 usually)
        // Expected queries:
        // 1. Get user (Auth)
        // 2. Count NCRs (pagination)
        // 3. Select NCRs
        // 4. Select Departments (Finder) - Eager loaded
        // 5. Select Departments (Receiver) - Eager loaded
        // 6. Select Users (Created By) - Eager loaded
        // 7. Select Defect Categories - Eager loaded
        // 8. Select Severity Levels - Eager loaded
        // etc.
        
        // If N+1 exists, query count would be > 20
        
        $this->assertLessThan(15, $queryCount, "Potential N+1 query problem detected. Query count: $queryCount");
    }

    public function test_api_response_time()
    {
        NCR::factory()->count(50)->create();
        
        $start = microtime(true);
        
        $response = $this->actingAsApi($this->user)
            ->getJson('/api/ncrs');
            
        $end = microtime(true);
        $duration = ($end - $start) * 1000; // in ms
        
        $response->assertStatus(200);
        
        // Assert response time is under 500ms (generous for local test, but good baseline)
        $this->assertLessThan(500, $duration, "API response time too slow: {$duration}ms");
    }
}
