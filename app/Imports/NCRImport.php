<?php

namespace App\Imports;

use App\Models\NCR;
use App\Models\Department;
use App\Models\DefectCategory;
use App\Models\SeverityLevel;
use App\Models\DispositionMethod;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NCRImport implements ToModel, WithHeadingRow
{
    private $headerRowIndex;

    public function __construct(int $headerRowIndex = 1)
    {
        $this->headerRowIndex = $headerRowIndex;
    }

    public function headingRow(): int
    {
        return $this->headerRowIndex;
    }

    /**
     * Helper to get value from row with multiple possible keys
     */
    private function getValue($row, $keys, $default = null)
    {
        foreach ($keys as $key) {
            // Check both original key and slugified key (Laravel Excel slugifies headers)
            if (isset($row[$key]) && !empty($row[$key])) {
                return $row[$key];
            }
            // Also check for cleaned keys (e.g. "NCR No." -> "ncr_no")
            // But WithHeadingRow already does slugification.
        }
        return $default;
    }

    private function resolveDepartmentId(?string $nameOrCode): ?int
    {
        if (!$nameOrCode) return null;
        $raw = trim($nameOrCode);
        $upper = strtoupper($raw);
        $map = [
            'QUALITY CONTROL' => 'QC',
            'QC' => 'QC',
            'PRODUCTION' => 'PROD',
            'PROD' => 'PROD',
            'PURCHASING' => 'PURCH',
            'PURCH' => 'PURCH',
            'ENGINEERING' => 'ENG',
            'ENG' => 'ENG',
            'INFORMATION TECHNOLOGY' => 'IT',
            'IT' => 'IT',
            'WAREHOUSE' => 'WH',
            'MAINTENANCE' => 'MAINT',
            'GENERAL AFFAIR' => 'GA',
            'DOCUMENTATION' => 'DOC',
            'MANAGEMENT' => 'MGMT',
            'FINANCE' => 'FIN',
            'SUPPLY CHAIN MANAGEMENT' => 'SCM',
        ];
        $code = $map[$upper] ?? $raw;
        $dept = Department::where('department_code', $code)
            ->orWhere('department_name', $raw)
            ->first();
        return $dept ? $dept->id : null;
    }

    private function resolveUserIdByName(?string $name): ?int
    {
        if (!$name) return null;
        $clean = trim($name);
        $user = User::where('name', 'like', $clean)
            ->orWhere('email', 'like', $clean)
            ->first();
        return $user ? $user->id : null;
    }

    public function model(array $row)
    {
        // Try to find NCR Number key
        $ncrNo = $this->getValue($row, ['ncr_no', 'ncr_number', 'no_ncr', 'nomor_ncr', 'no']);
        
        // Skip if no NCR Number
        if (!$ncrNo) {
            return null;
        }

        // Check for existing NCR to avoid Duplicate Entry error
        $existingNcr = NCR::where('ncr_number', $ncrNo)->first();
        if ($existingNcr) {
            // Option: Skip or Update?
            // For now, let's return null to skip it silently, or we could update it.
            // Returning null means "do nothing for this row"
            return null; 
        }

        // 1. Finder Dept
        $finderDeptName = $this->getValue($row, ['finder_dept', 'finder_department', 'dept_finder', 'department_finder', 'finder']);
        $finderDeptId = $this->resolveDepartmentId($finderDeptName) ?? 1;

        // 2. Receiver Dept
        $receiverDeptName = $this->getValue($row, ['receiver_dept', 'receiver_department', 'dept_receiver', 'department_receiver', 'receiver', 'to_receiver_dept']);
        $receiverDeptId = $this->resolveDepartmentId($receiverDeptName) ?? 1;

        // 3. Defect Category
        $defectGroupName = $this->getValue($row, ['defect_group', 'defect_category', 'category', 'group']);
        $defectCategory = DefectCategory::where('category_name', $defectGroupName)->first();
        $defectCategoryId = $defectCategory ? $defectCategory->id : 1;

        // 4. Severity
        $severityName = $this->getValue($row, ['severity', 'severity_level', 'level']);
        $severityLevel = SeverityLevel::where('level_name', $severityName)->first();
        $severityLevelId = $severityLevel ? $severityLevel->id : 1;

        // 5. Disposition
        // Added 'decision_disposition' just in case. Note that Excel columns can be totally lowercased by Maatwebsite.
        // We look for 'disposition', 'decision', 'disposition_method'
        $dispositionName = $this->getValue($row, ['disposition', 'decision', 'disposition_method', 'decision_disposition', 'id_disposition', 'id_decision']);
        $dispositionMethod = null;
        if ($dispositionName) {
            $dispositionNameStr = (string) $dispositionName;
            
            // Allow parsing exact IDs if they put "ID 1", "1", "ID 7" in Excel
            if (preg_match('/^(?:id\s*)?(\d+)$/i', trim($dispositionNameStr), $matches)) {
                $dispositionMethod = DispositionMethod::find($matches[1]);
            } else {
                $dispositionMethod = DispositionMethod::where('method_name', $dispositionNameStr)->first();
                if (!$dispositionMethod) {
                    // Try simple mapping
                    $map = [
                        'repaired' => 'Repaired',
                        'repair' => 'Repaired',
                        'use as it is' => 'Use As Is',
                        'use as is' => 'Use As Is',
                        'use_as_is' => 'Use As Is',
                        'rejected and make new' => 'Rejected',
                        'rejected' => 'Rejected',
                        'reject' => 'Rejected',
                    ];
                    $key = strtolower(trim($dispositionNameStr));
                    $guess = $map[$key] ?? null;
                    if ($guess) {
                        $dispositionMethod = DispositionMethod::where('method_name', $guess)->first();
                    }
                }
            }
        }

        // 6. Dates
        $dateValue = $this->getValue($row, ['date', 'date_found', 'tanggal', 'issue_date']);
        $issuedDateValue = $this->getValue($row, ['issued_date', 'date_issued', 'date_of_issue']);
        $caFinishDateValue = $this->getValue($row, ['ca_finish_date', 'ca_finish_date_', 'ca_finish_date__', 'ca_finish_date___', 'ca_finish_date____', 'ca_finish_date_____', 'ca_finish_date______', 'ca_finish_date_______', 'ca_finish_date________', 'ca finish date']);
        
        try {
            // Handle Excel serial date or string
            $dateFound = $dateValue ? (is_numeric($dateValue) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue) : Carbon::parse($dateValue)) : now();
            $issuedDate = $issuedDateValue ? (is_numeric($issuedDateValue) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($issuedDateValue) : Carbon::parse($issuedDateValue)) : $dateFound;
            $caFinishDate = $caFinishDateValue ? (is_numeric($caFinishDateValue) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($caFinishDateValue) : Carbon::parse($caFinishDateValue)) : null;
        } catch (\Exception $e) {
            $dateFound = now();
            $issuedDate = now();
            $caFinishDate = null;
        }

        // 7. Costs (Sanitize currency strings if needed, e.g. "$ 100")
        $cleanCost = function($val) {
            if (is_string($val)) {
                return (float) preg_replace('/[^0-9.-]/', '', $val);
            }
            return (float) $val;
        };
        
        // 8. Optional fields from old format
        $evalEffectiveness = $this->getValue($row, ['evaluation_of_effectiveness', 'evaluation', 'effectiveness']);
        $verificationRemarks = $evalEffectiveness ?: null;
        $effectivenessVerified = false;
        if (is_string($evalEffectiveness)) {
            $lower = strtolower($evalEffectiveness);
            $effectivenessVerified = str_contains($lower, 'verified') || str_contains($lower, 'no more same issue');
        }

        $assignedPicName = $this->getValue($row, ['assigned_pic', 'pic_of_ca', 'pic', 'pic_of_ca_']);
        $assignedPicId = $this->resolveUserIdByName($assignedPicName);
        $finderName = $this->getValue($row, ['finder', 'finder_name']);
        $finderUserId = $this->resolveUserIdByName($finderName) ?? (Auth::id() ?? null);
        $finderManagerName = $this->getValue($row, ['finder_manager', 'finder_manager_name']);
        $finderManagerId = $this->resolveUserIdByName($finderManagerName);
        $receiverComments = $this->getValue($row, ['receiver_comments', 'receiver_statements', 'receivers_comments_statements']);

        return new NCR([
            'ncr_number' => $ncrNo,
            'line_no' => $this->getValue($row, ['line_no', 'line']),
            'date_found' => $dateFound,
            'issued_date' => $issuedDate,
            'last_ncr_no' => $this->getValue($row, ['last_ncr_no', 'last_no']),
            
            'project_sn' => $this->getValue($row, ['project_sn', 'sn', 'serial_number', 'project_number']),
            'project_name' => $this->getValue($row, ['project_name', 'project']),
            'order_number' => $this->getValue($row, ['order_no', 'order_number', 'po_no']),
            'customer_name' => $this->getValue($row, ['customer', 'customer_name']),
            
            'part_name' => $this->getValue($row, ['part_name', 'part_no']),
            'product_description' => $this->getValue($row, ['product_description', 'description', 'description_of_non_conformance']) ?? $this->getValue($row, ['part_name']),
            'drawing_number' => $this->getValue($row, ['dwg_doc_no', 'dwg', 'doc_no', 'drawing_number']),
            
            'finder_dept_id' => $finderDeptId,
            'receiver_dept_id' => $receiverDeptId,
            'location_found' => $this->getValue($row, ['location', 'location_found']),
            'defect_location' => $this->getValue($row, ['defect_location', 'defect_area', 'defect_area_source_of_defect', 'source_of_defect']),
            
            'defect_description' => $this->getValue($row, ['description', 'defect_description', 'problem', 'description_of_non_conformance']) ?? 'Imported Data',
            'defect_mode' => $this->getValue($row, ['defect_mode', 'mode']),
            'severity_level_id' => $severityLevelId,
            'defect_category_id' => $defectCategoryId,
            'quantity_affected' => (int) $this->getValue($row, ['qty', 'quantity', 'quantity_affected'], 0),
            
            'disposition_method_id' => $dispositionMethod ? $dispositionMethod->id : null,
            'immediate_action' => $this->getValue($row, ['corrected_action', 'immediate_action', 'action', 'corrective_action', 'corrective_action_ca']),
            'ca_finish_date' => $caFinishDate,
            'assigned_pic_id' => $assignedPicId,
            'finder_manager_id' => $finderManagerId,
            'receiver_assignment_remarks' => $receiverComments,
            
            // Costs
            'mh_used' => $cleanCost($this->getValue($row, ['mh_used', 'man_hours', 'hours'])),
            'mh_rate' => $cleanCost($this->getValue($row, ['mh_rate', 'rate'])),
            'labor_cost' => $cleanCost($this->getValue($row, ['labor_cost', 'labour_cost'])),
            'material_cost' => $cleanCost($this->getValue($row, ['material_cost'])),
            'subcont_cost' => $cleanCost($this->getValue($row, ['subcont_cost'])),
            'engineering_cost' => $cleanCost($this->getValue($row, ['eng_cost', 'engineering_cost'])),
            'other_cost' => $cleanCost($this->getValue($row, ['other_cost', 'othet_cost'])),
            'total_cost' => $cleanCost($this->getValue($row, ['total_cost'])),
            
            'root_cause' => $this->getValue($row, ['root_cause', 'rca']),
            'preventive_action' => $this->getValue($row, ['preventive_action', 'pa']),
            'status' => $this->getValue($row, ['status']) ?? 'Draft',
            'related_document' => $this->getValue($row, ['related_doc', 'related_document', 'document_related']),
            'verification_remarks' => $verificationRemarks,
            'effectiveness_verified' => $effectivenessVerified,
            
            'created_by_user_id' => $finderUserId ?? (Auth::id() ?? 1),
        ]);
    }
}
