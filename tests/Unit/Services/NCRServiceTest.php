<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\NCR;
use App\Models\Department;
use App\Models\DefectCategory;
use App\Models\SeverityLevel;
use App\Services\NCRService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\NCRCreated;
use App\Events\NCRStatusChanged;

class NCRServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $ncrService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ncrService = new NCRService();
    }

    public function test_create_ncr_successfully()
    {
        Event::fake();

        $user = User::factory()->create();
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

        $ncr = $this->ncrService->createNCR($data, $user);

        $this->assertInstanceOf(NCR::class, $ncr);
        $this->assertEquals('Draft', $ncr->status);
        $this->assertEquals($user->id, $ncr->created_by_user_id);
        $this->assertStringContainsString('QC', $ncr->ncr_number);
        
        $this->assertDatabaseHas('ncrs', [
            'id' => $ncr->id,
            'order_number' => 'ORD-123',
            'status' => 'Draft'
        ]);

        Event::assertDispatched(NCRCreated::class);
    }

    public function test_submit_ncr_for_approval()
    {
        Event::fake();

        $user = User::factory()->create();
        $ncr = NCR::factory()->create([
            'status' => 'Draft',
            'created_by_user_id' => $user->id
        ]);

        $submittedNcr = $this->ncrService->submitForApproval($ncr, $user);

        $this->assertEquals('Pending_Finder_Approval', $submittedNcr->status);
        Event::assertDispatched(NCRStatusChanged::class);
    }

    public function test_approve_ncr()
    {
        Event::fake();

        $manager = User::factory()->create(); 
        // Assuming we might need specific roles/permissions later, but for unit test of service logic often we mock or ensure the user satisfies gate checks if service uses them.
        // Looking at NCRService, it usually handles the business logic. Access control might be in Controller or Policy. 
        // If Service checks policy, we need to handle that. 
        // Let's assume for now Service focuses on data mutation.
        
        $ncr = NCR::factory()->create(['status' => 'Pending_Finder_Approval']);

        $approvedNcr = $this->ncrService->approveNCR($ncr, $manager, 'Approved by manager');

        $this->assertEquals('Pending_QC_Registration', $approvedNcr->status); 
        $this->assertDatabaseHas('comments', [
            'commentable_id' => $ncr->id,
            'commentable_type' => 'App\Models\NCR',
            'comment_text' => 'Approved by manager'
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'entity_id' => $ncr->id,
            'entity_type' => 'NCR',
            'action_type' => 'Approved'
        ]);

        Event::assertDispatched(NCRStatusChanged::class);
    }

    public function test_reject_ncr()
    {
        Event::fake();

        $manager = User::factory()->create();
        $ncr = NCR::factory()->create(['status' => 'Pending_Finder_Approval']);

        $rejectedNcr = $this->ncrService->rejectNCR($ncr, $manager, 'Invalid data');

        $this->assertEquals('Draft', $rejectedNcr->status); 
        $this->assertDatabaseHas('comments', [
            'commentable_id' => $ncr->id,
            'commentable_type' => 'App\Models\NCR',
            'comment_text' => 'Rejected: Invalid data'
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'entity_id' => $ncr->id,
            'entity_type' => 'NCR',
            'action_type' => 'Rejected'
        ]);

        Event::assertDispatched(NCRStatusChanged::class);
    }
}
