<?php

namespace Tests\Unit\Models;

use App\Models\DefectCategory;
use App\Models\Department;
use App\Models\DispositionMethod;
use App\Models\NCR;
use App\Models\SeverityLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NCRTest extends TestCase
{
    use RefreshDatabase;

    public function test_ncr_can_be_created()
    {
        $ncr = NCR::factory()->create([
            'ncr_number' => 'NCR-TEST-001',
            'status' => 'Draft',
        ]);

        $this->assertDatabaseHas('ncrs', [
            'ncr_number' => 'NCR-TEST-001',
            'status' => 'Draft',
        ]);
    }

    public function test_ncr_belongs_to_finder_department()
    {
        $department = Department::factory()->create();
        $ncr = NCR::factory()->create(['finder_dept_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $ncr->finderDepartment);
        $this->assertEquals($department->id, $ncr->finderDepartment->id);
    }

    public function test_ncr_belongs_to_receiver_department()
    {
        $department = Department::factory()->create();
        $ncr = NCR::factory()->create(['receiver_dept_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $ncr->receiverDepartment);
        $this->assertEquals($department->id, $ncr->receiverDepartment->id);
    }

    public function test_ncr_belongs_to_creator()
    {
        $user = User::factory()->create();
        $ncr = NCR::factory()->create(['created_by_user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $ncr->createdBy);
        $this->assertEquals($user->id, $ncr->createdBy->id);
    }

    public function test_ncr_belongs_to_defect_category()
    {
        $category = DefectCategory::factory()->create();
        $ncr = NCR::factory()->create(['defect_category_id' => $category->id]);

        $this->assertInstanceOf(DefectCategory::class, $ncr->defectCategory);
        $this->assertEquals($category->id, $ncr->defectCategory->id);
    }

    public function test_ncr_belongs_to_severity_level()
    {
        $level = SeverityLevel::factory()->create();
        $ncr = NCR::factory()->create(['severity_level_id' => $level->id]);

        $this->assertInstanceOf(SeverityLevel::class, $ncr->severityLevel);
        $this->assertEquals($level->id, $ncr->severityLevel->id);
    }

    public function test_ncr_belongs_to_disposition_method()
    {
        $method = DispositionMethod::factory()->create();
        $ncr = NCR::factory()->create(['disposition_method_id' => $method->id]);

        $this->assertInstanceOf(DispositionMethod::class, $ncr->dispositionMethod);
        $this->assertEquals($method->id, $ncr->dispositionMethod->id);
    }

    public function test_scope_by_status()
    {
        NCR::factory()->create(['status' => 'Draft']);
        NCR::factory()->create(['status' => 'Submitted']);
        NCR::factory()->create(['status' => 'Draft']);

        $this->assertEquals(2, NCR::byStatus('Draft')->count());
        $this->assertEquals(1, NCR::byStatus('Submitted')->count());
    }

    public function test_scope_overdue()
    {
        // Overdue NCR
        NCR::factory()->create([
            'status' => 'Submitted',
            'target_closure_date' => now()->subDay(),
        ]);

        // Not overdue (future date)
        NCR::factory()->create([
            'status' => 'Submitted',
            'target_closure_date' => now()->addDay(),
        ]);

        // Not overdue (closed)
        NCR::factory()->create([
            'status' => 'Closed',
            'target_closure_date' => now()->subDay(),
            'closed_at' => now(),
        ]);

        $this->assertEquals(1, NCR::overdue()->count());
    }

    public function test_scope_pending_approval()
    {
        NCR::factory()->create(['status' => 'Pending_Finder_Approval']);
        NCR::factory()->create(['status' => 'Pending_QC_Registration']);
        NCR::factory()->create(['status' => 'Draft']); // Not pending approval

        $this->assertEquals(2, NCR::pendingApproval()->count());
    }

    public function test_is_overdue_accessor()
    {
        $overdueNcr = NCR::factory()->create([
            'status' => 'Submitted',
            'target_closure_date' => now()->subDay(),
        ]);

        $onTimeNcr = NCR::factory()->create([
            'status' => 'Submitted',
            'target_closure_date' => now()->addDay(),
        ]);

        $closedOverdueNcr = NCR::factory()->create([
            'status' => 'Closed',
            'target_closure_date' => now()->subDay(),
            'closed_at' => now(),
        ]);

        $this->assertTrue($overdueNcr->is_overdue);
        $this->assertFalse($onTimeNcr->is_overdue);
        $this->assertFalse($closedOverdueNcr->is_overdue);
    }

    public function test_days_open_accessor()
    {
        $createdDate = now()->subDays(5);
        $ncr = NCR::factory()->create([
            'created_at' => $createdDate,
            'status' => 'Submitted',
        ]);

        // Using round/int cast because diffInDays returns integer but timing might be slightly off in tests
        $this->assertEquals(5, $ncr->days_open);

        $closedDate = now()->subDays(2);
        $closedNcr = NCR::factory()->create([
            'created_at' => $createdDate,
            'closed_at' => $closedDate,
            'status' => 'Closed',
        ]);

        $this->assertEquals(3, $closedNcr->days_open);
    }
}
