<?php

namespace Database\Seeders;

use App\Models\CAPA;
use App\Models\DefectCategory;
use App\Models\Department;
use App\Models\DispositionMethod;
use App\Models\NCR;
use App\Models\SeverityLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $qc = Department::where('department_code', 'QC')->first();
        $scm = Department::where('department_code', 'SCM')->first();
        $sales = Department::where('department_code', 'SALES')->first();
        $eng = Department::where('department_code', 'ENG')->first();

        $admin = User::where('email', 'admin.ncr@tab-indonesia.co.id')->first();
        $qcManager = User::where('email', 'qc.manager@tab-indonesia.co.id')->first();
        $procManager = User::where('email', 'proc.manager@tab-indonesia.co.id')->first();
        $finderUser = User::where('email', 'qc.inspector1@tab-indonesia.co.id')->first();
        $receiverUser = User::where('email', 'purch.specialist@tab-indonesia.co.id')->first();

        if (!$qc || !$scm || !$admin || !$qcManager || !$procManager || !$finderUser || !$receiverUser) {
            return;
        }

        $defectCategory = DefectCategory::inRandomOrder()->first();
        $severityMinor = SeverityLevel::where('level_code', 'MIN')->first() ?? SeverityLevel::first();
        $severityMajor = SeverityLevel::where('level_code', 'MAJ')->first() ?? SeverityLevel::first();
        $severityCrit = SeverityLevel::where('level_code', 'CRIT')->first() ?? SeverityLevel::first();
        $dispRework = DispositionMethod::where('method_code', 'RWK')->first();

        $now = now();

        $makeNumber = function (int $i): string {
            return sprintf('NCR-DEMO-%04d', $i);
        };

        $ncr1 = NCR::firstOrCreate(
            ['ncr_number' => $makeNumber(1)],
            [
                'order_number' => 'ORD-DEMO-001',
                'project_name' => 'Demo Project A',
                'customer_name' => 'Demo Customer',
                'product_description' => 'Demo product description',
                'drawing_number' => 'DWG-DEMO-001',
                'material_specification' => 'SS304',
                'date_found' => $now->copy()->subDays(10)->toDateString(),
                'location_found' => 'Workshop',
                'quantity_affected' => 3,
                'finder_dept_id' => $qc->id,
                'receiver_dept_id' => $scm->id,
                'created_by_user_id' => $finderUser->id,
                'defect_category_id' => $defectCategory?->id,
                'defect_description' => 'Supplier material specification mismatch',
                'defect_location' => 'Incoming Inspection',
                'severity_level_id' => $severityMajor?->id,
                'status' => 'Open',
                'submitted_at' => $now->copy()->subDays(9),
            ]
        );

        $ncr2 = NCR::firstOrCreate(
            ['ncr_number' => $makeNumber(2)],
            [
                'order_number' => 'ORD-DEMO-002',
                'project_name' => 'Demo Project B',
                'customer_name' => 'Demo Customer',
                'product_description' => 'Demo product description',
                'drawing_number' => 'DWG-DEMO-002',
                'material_specification' => 'SS316',
                'date_found' => $now->copy()->subDays(7)->toDateString(),
                'location_found' => 'Workshop',
                'quantity_affected' => 1,
                'finder_dept_id' => $sales?->id ?? $qc->id,
                'receiver_dept_id' => $scm->id,
                'created_by_user_id' => $admin->id,
                'defect_category_id' => $defectCategory?->id,
                'defect_description' => 'Wrong part delivered by supplier',
                'defect_location' => 'Supplier',
                'severity_level_id' => $severityCrit?->id,
                'status' => 'Pending_Finder_Approval',
                'submitted_at' => $now->copy()->subDays(6),
                'finder_manager_id' => $qcManager->id,
            ]
        );

        $ncr3 = NCR::firstOrCreate(
            ['ncr_number' => $makeNumber(3)],
            [
                'order_number' => 'ORD-DEMO-003',
                'project_name' => 'Demo Project C',
                'customer_name' => 'Demo Customer',
                'product_description' => 'Demo product description',
                'drawing_number' => 'DWG-DEMO-003',
                'material_specification' => 'SS304',
                'date_found' => $now->copy()->subDays(20)->toDateString(),
                'location_found' => 'Fabrication',
                'quantity_affected' => 2,
                'finder_dept_id' => $eng?->id ?? $qc->id,
                'receiver_dept_id' => $scm->id,
                'created_by_user_id' => $admin->id,
                'defect_category_id' => $defectCategory?->id,
                'defect_description' => 'Drawing revision not followed',
                'defect_location' => 'Engineering',
                'severity_level_id' => $severityMinor?->id,
                'status' => 'Closed',
                'submitted_at' => $now->copy()->subDays(19),
                'closed_at' => $now->copy()->subDays(3),
                'closed_by_user_id' => $qcManager->id,
                'closure_remarks' => 'Corrected and verified.',
            ]
        );

        $ncr4 = NCR::firstOrCreate(
            ['ncr_number' => $makeNumber(4)],
            [
                'order_number' => 'ORD-DEMO-004',
                'project_name' => 'Demo Project D',
                'customer_name' => 'Demo Customer',
                'product_description' => 'Demo product description',
                'drawing_number' => 'DWG-DEMO-004',
                'material_specification' => 'SS304',
                'date_found' => $now->copy()->subDays(12)->toDateString(),
                'location_found' => 'Incoming',
                'quantity_affected' => 5,
                'finder_dept_id' => $qc->id,
                'receiver_dept_id' => $scm->id,
                'created_by_user_id' => $finderUser->id,
                'defect_category_id' => $defectCategory?->id,
                'defect_description' => 'Supplier certificate missing',
                'defect_location' => 'Supplier',
                'severity_level_id' => $severityMajor?->id,
                'status' => 'Sent_To_Receiver',
                'assigned_pic_id' => $receiverUser->id,
                'pic_assigned_at' => $now->copy()->subDays(11),
                'receiver_manager_id' => $procManager->id,
                'receiver_assigned_at' => $now->copy()->subDays(11),
                'disposition_method_id' => $dispRework?->id,
                'receiver_assignment_remarks' => 'Assign PIC to investigate supplier documents.',
            ]
        );

        $ncr5 = NCR::firstOrCreate(
            ['ncr_number' => $makeNumber(5)],
            [
                'order_number' => 'ORD-DEMO-005',
                'project_name' => 'Demo Project E',
                'customer_name' => 'Demo Customer',
                'product_description' => 'Demo product description',
                'drawing_number' => 'DWG-DEMO-005',
                'material_specification' => 'SS316',
                'date_found' => $now->copy()->subDays(15)->toDateString(),
                'location_found' => 'Incoming',
                'quantity_affected' => 4,
                'finder_dept_id' => $qc->id,
                'receiver_dept_id' => $scm->id,
                'created_by_user_id' => $finderUser->id,
                'defect_category_id' => $defectCategory?->id,
                'defect_description' => 'Repeat issue: incorrect material grade from supplier',
                'defect_location' => 'Supplier',
                'severity_level_id' => $severityCrit?->id,
                'status' => 'CAPA_In_Progress',
                'assigned_pic_id' => $receiverUser->id,
                'pic_assigned_at' => $now->copy()->subDays(14),
                'receiver_manager_id' => $procManager->id,
                'receiver_assigned_at' => $now->copy()->subDays(14),
                'disposition_method_id' => $dispRework?->id,
                'receiver_assignment_remarks' => 'CAPA required due to recurrence.',
            ]
        );

        CAPA::firstOrCreate(
            ['ncr_id' => $ncr5->id],
            [
                'capa_number' => CAPA::generateCapaNumber(),
                'rca_method' => '5 Why',
                'root_cause_summary' => 'Supplier process control not adequate.',
                'why_1' => 'Wrong grade delivered',
                'why_2' => 'Supplier mixed batches',
                'why_3' => 'No segregation at warehouse',
                'why_4' => 'SOP not enforced',
                'why_5' => 'Lack of training and audits',
                'corrective_action_plan' => 'Audit supplier and enforce segregation controls.',
                'preventive_action_plan' => 'Monthly supplier audit and incoming verification checklist.',
                'expected_outcome' => 'Zero recurrence for the next monitoring period.',
                'assigned_pic_id' => $receiverUser->id,
                'assigned_by_user_id' => $procManager->id,
                'assigned_at' => $now->copy()->subDays(13),
                'target_completion_date' => $now->copy()->addDays(14)->toDateString(),
                'progress_percentage' => 60,
                'current_status' => 'In_Progress',
            ]
        );

        $ncr6 = NCR::firstOrCreate(
            ['ncr_number' => $makeNumber(6)],
            [
                'order_number' => 'ORD-DEMO-006',
                'project_name' => 'Demo Project F',
                'customer_name' => 'Demo Customer',
                'product_description' => 'Demo product description',
                'drawing_number' => 'DWG-DEMO-006',
                'material_specification' => 'SS304',
                'date_found' => $now->copy()->subDays(25)->toDateString(),
                'location_found' => 'Incoming',
                'quantity_affected' => 2,
                'finder_dept_id' => $qc->id,
                'receiver_dept_id' => $scm->id,
                'created_by_user_id' => $finderUser->id,
                'defect_category_id' => $defectCategory?->id,
                'defect_description' => 'CAPA finished and awaiting verification',
                'defect_location' => 'Supplier',
                'severity_level_id' => $severityMajor?->id,
                'status' => 'CAPA_In_Progress',
                'assigned_pic_id' => $receiverUser->id,
                'pic_assigned_at' => $now->copy()->subDays(24),
                'receiver_manager_id' => $procManager->id,
                'receiver_assigned_at' => $now->copy()->subDays(24),
                'disposition_method_id' => $dispRework?->id,
                'receiver_assignment_remarks' => 'Waiting QC verification after completion.',
            ]
        );

        CAPA::firstOrCreate(
            ['ncr_id' => $ncr6->id],
            [
                'capa_number' => CAPA::generateCapaNumber(),
                'rca_method' => 'Fishbone',
                'root_cause_summary' => 'Multiple contributing factors.',
                'corrective_action_plan' => 'Implement incoming verification and supplier corrective actions.',
                'preventive_action_plan' => 'Supplier KPI and penalty clause.',
                'expected_outcome' => 'No recurrence within monitoring period.',
                'assigned_pic_id' => $receiverUser->id,
                'assigned_by_user_id' => $procManager->id,
                'assigned_at' => $now->copy()->subDays(23),
                'target_completion_date' => $now->copy()->subDays(5)->toDateString(),
                'actual_completion_date' => $now->copy()->subDays(6)->toDateString(),
                'progress_percentage' => 100,
                'current_status' => 'Pending_Verification',
            ]
        );
    }
}

