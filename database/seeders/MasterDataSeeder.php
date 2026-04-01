<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Department;
use App\Models\DefectCategory;
use App\Models\SeverityLevel;
use App\Models\DispositionMethod;
use App\Models\Setting;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['role_name' => 'Super Admin', 'display_name' => 'Super Administrator', 'description' => 'Full access to all system features', 'level' => 5],
            ['role_name' => 'Administrator', 'display_name' => 'Administrator', 'description' => 'System administrator with limited master data access', 'level' => 5],
            ['role_name' => 'Department Manager', 'display_name' => 'Department Manager', 'description' => 'Approver for NCRs', 'level' => 3],
            ['role_name' => 'QC Manager', 'display_name' => 'QC Manager', 'description' => 'Verifies and closes NCRs/CAPAs', 'level' => 4],
            ['role_name' => 'User', 'display_name' => 'Standard User', 'description' => 'Standard user who can report NCRs', 'level' => 1],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role_name' => $role['role_name']], $role);
        }

        // 2. Departments (Updated with image data)
        $departments = [
            ['department_code' => 'IT', 'department_name' => 'Information Technology'],
            ['department_code' => 'QC', 'department_name' => 'Quality Control'],
            ['department_code' => 'PROD', 'department_name' => 'Production'],
            ['department_code' => 'ENG', 'department_name' => 'Engineering'],
            ['department_code' => 'HR', 'department_name' => 'Human Resources'],
            ['department_code' => 'SCM', 'department_name' => 'Supply Chain Management'],
            ['department_code' => 'PURCH', 'department_name' => 'Purchasing'],
            ['department_code' => 'SALES', 'department_name' => 'Sales'],
            ['department_code' => 'WH', 'department_name' => 'Warehouse'],
            ['department_code' => 'MAINT', 'department_name' => 'Maintenance'],
            ['department_code' => 'DOC', 'department_name' => 'Documentation'],
            ['department_code' => 'GA', 'department_name' => 'General Affair'],
            ['department_code' => 'MGMT', 'department_name' => 'Management'],
            ['department_code' => 'FIN', 'department_name' => 'Finance'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['department_code' => $dept['department_code']], $dept);
        }

        // 3. Defect Categories (Updated with image data - "Defect Group")
        // Note: DefectModeSeeder handles the detailed modes, this handles the high-level groups
        $categories = [
            // Standard
            ['category_code' => 'MAT', 'category_name' => 'Material Defect', 'description' => 'Issues with raw materials'],
            ['category_code' => 'PROC', 'category_name' => 'Process Deviation', 'description' => 'Failure to follow SOP'],
            ['category_code' => 'EQUIP', 'category_name' => 'Equipment Failure', 'description' => 'Machine breakdown or malfunction'],
            ['category_code' => 'HUMAN', 'category_name' => 'Human Error', 'description' => 'Mistakes by operators'],
            ['category_code' => 'DOC', 'category_name' => 'Documentation', 'description' => 'Missing or incorrect records'],
            
            // From Image (Defect Group)
            ['category_code' => 'NONE', 'category_name' => 'None', 'description' => 'None'],
            ['category_code' => 'MANUF', 'category_name' => 'Manuf.', 'description' => 'Manufacturing'],
            ['category_code' => 'WELD', 'category_name' => 'Welding', 'description' => 'Welding'],
            ['category_code' => 'PURCH', 'category_name' => 'Purch', 'description' => 'Purchasing'],
            ['category_code' => 'DSG', 'category_name' => 'Dsg.', 'description' => 'Design'],
            ['category_code' => 'ELECT', 'category_name' => 'Elect', 'description' => 'Electrical'],
            
            // Additional Categories from Image 2
            ['category_code' => 'ASSY', 'category_name' => 'Assembling', 'description' => 'Assembling'],
            ['category_code' => 'EXT', 'category_name' => 'External', 'description' => 'External'],
            ['category_code' => 'INT', 'category_name' => 'Internal', 'description' => 'Internal'],
            ['category_code' => 'PREP', 'category_name' => 'Preparation', 'description' => 'Preparation'],
            ['category_code' => 'SUBC', 'category_name' => 'Subcont', 'description' => 'Subcontractor'],
            ['category_code' => 'SUPP', 'category_name' => 'Supplier', 'description' => 'Supplier'],
            ['category_code' => 'TEST', 'category_name' => 'Testing', 'description' => 'Testing'],
        ];

        foreach ($categories as $cat) {
            DefectCategory::firstOrCreate(['category_name' => $cat['category_name']], $cat);
        }

        // 4. Severity Levels
        $severities = [
            ['level_code' => 'MIN', 'level_name' => 'Minor', 'description' => 'Low impact, easy to fix', 'priority' => 1, 'color_code' => 'bg-green-100'],
            ['level_code' => 'MAJ', 'level_name' => 'Major', 'description' => 'Significant impact, requires formal CAPA', 'priority' => 2, 'color_code' => 'bg-yellow-100'],
            ['level_code' => 'CRIT', 'level_name' => 'Critical', 'description' => 'Safety risk or major financial loss', 'priority' => 3, 'color_code' => 'bg-red-100'],
        ];

        foreach ($severities as $sev) {
            SeverityLevel::firstOrCreate(['level_code' => $sev['level_code']], $sev);
        }

        // 5. Disposition Methods (Updated with image data - "Category")
        $methods = [
            ['method_code' => 'UAI', 'method_name' => 'Use As Is', 'description' => 'Accept with deviation approval'],
            ['method_code' => 'RWK', 'method_name' => 'Rework', 'description' => 'Fix to meet specifications'],
            ['method_code' => 'REP', 'method_name' => 'Repaired', 'description' => 'Fix to functional state (but not original spec)'], // "Repaired" from image
            ['method_code' => 'REJ', 'method_name' => 'Rejected', 'description' => 'Rejected and make new'], // "Rejected" from image
            ['method_code' => 'RTV', 'method_name' => 'Return to Vendor', 'description' => 'Send back to supplier'],
        ];

        foreach ($methods as $method) {
            DispositionMethod::updateOrCreate(
                ['method_code' => $method['method_code']], 
                $method
            );
        }

        // 6. System Settings (Defaults)
        $settings = [
            ['setting_key' => 'company_name', 'setting_value' => 'PT. Topsystem Asia Base', 'setting_type' => 'string', 'category' => 'General', 'description' => 'Company name', 'is_public' => true],
            ['setting_key' => 'company_address', 'setting_value' => 'Kawasan Industri Bekasi, Indonesia', 'setting_type' => 'string', 'category' => 'General', 'description' => 'Company address', 'is_public' => true],
            ['setting_key' => 'public_base_url', 'setting_value' => '', 'setting_type' => 'string', 'category' => 'General', 'description' => 'Public base URL for QR/links (e.g., http://192.168.1.10:8000)', 'is_public' => true],
            ['setting_key' => 'public_link_expires_days', 'setting_value' => '7', 'setting_type' => 'integer', 'category' => 'General', 'description' => 'Days until QR public link expires', 'is_public' => true],
            ['setting_key' => 'ncr_number_format', 'setting_value' => 'YY.PXX-DDD-NN', 'setting_type' => 'string', 'category' => 'NCR', 'description' => 'NCR number format template', 'is_public' => false],
            ['setting_key' => 'capa_number_format', 'setting_value' => 'CAPA-YY-NNNN', 'setting_type' => 'string', 'category' => 'CAPA', 'description' => 'CAPA number format template', 'is_public' => false],
            ['setting_key' => 'approval_timeout_hours', 'setting_value' => '24', 'setting_type' => 'integer', 'category' => 'Workflow', 'description' => 'Hours before approval reminder sent', 'is_public' => false],
            ['setting_key' => 'capa_deadline_warning_days', 'setting_value' => '3', 'setting_type' => 'integer', 'category' => 'CAPA', 'description' => 'Days before deadline to send warning', 'is_public' => false],
            ['setting_key' => 'max_file_upload_size_mb', 'setting_value' => '5', 'setting_type' => 'integer', 'category' => 'Upload', 'description' => 'Maximum file size in MB', 'is_public' => false],
            ['setting_key' => 'allowed_file_types', 'setting_value' => 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'setting_type' => 'string', 'category' => 'Upload', 'description' => 'Allowed file extensions', 'is_public' => false],
            ['setting_key' => 'email_notifications_enabled', 'setting_value' => '1', 'setting_type' => 'boolean', 'category' => 'Email', 'description' => 'Enable email notifications', 'is_public' => false],
            ['setting_key' => 'dashboard_refresh_interval', 'setting_value' => '300', 'setting_type' => 'integer', 'category' => 'Dashboard', 'description' => 'Dashboard auto-refresh in seconds', 'is_public' => false],
            ['setting_key' => 'iso_certification', 'setting_value' => 'ISO 9001:2015', 'setting_type' => 'string', 'category' => 'Compliance', 'description' => 'ISO certification standard', 'is_public' => true],
            ['setting_key' => 'asme_certification', 'setting_value' => 'ASME Section VIII & IX', 'setting_type' => 'string', 'category' => 'Compliance', 'description' => 'ASME certification', 'is_public' => true],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(
                ['setting_key' => $s['setting_key']],
                [
                    'setting_value' => $s['setting_value'],
                    'setting_type' => $s['setting_type'],
                    'category' => $s['category'],
                    'description' => $s['description'],
                    'is_public' => $s['is_public'],
                ]
            );
        }
    }
}
