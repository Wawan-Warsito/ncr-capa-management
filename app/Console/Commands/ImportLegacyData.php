<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\NCR;
use App\Models\User;
use App\Models\Department;
use App\Models\DefectCategory;
use App\Models\SeverityLevel;
use App\Models\DispositionMethod;
use Carbon\Carbon;

class ImportLegacyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:legacy-data {file : Path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import legacy NCR data from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Starting import from: {$filePath}");

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file); // Assume first row is header

        // Map header columns to index
        // Adjust these based on actual CSV structure
        // Expected columns: NCR No, Date, Order No, Project Name, Customer, Finder Dept, Receiver Dept, Defect Category, Description, Severity, Disposition, Status, Created By
        
        $this->info("Header columns: " . implode(', ', $header));
        
        // Define mapping (column name => index) manually or dynamically
        // For simplicity, let's assume specific order or try to find index by name
        
        $map = $this->mapHeaders($header);
        
        DB::beginTransaction();
        
        try {
            $count = 0;
            while (($row = fgetcsv($file)) !== false) {
                $this->processRow($row, $map);
                $count++;
                
                if ($count % 10 == 0) {
                    $this->info("Processed {$count} records...");
                }
            }
            
            DB::commit();
            $this->info("Import completed successfully! Total records: {$count}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            Log::error("Import failed", ['exception' => $e]);
            return 1;
        } finally {
            fclose($file);
        }

        return 0;
    }

    private function mapHeaders($header)
    {
        // Normalize headers to lowercase for easier matching
        $header = array_map('strtolower', $header);
        
        // Return map of 'field_key' => index
        // Adjust these keys to match your CSV header names
        return [
            'ncr_number' => $this->findIndex($header, ['ncr no', 'ncr_no', 'number']),
            'date_found' => $this->findIndex($header, ['date', 'date found', 'date_found']),
            'order_number' => $this->findIndex($header, ['order no', 'order_no', 'po no']),
            'project_name' => $this->findIndex($header, ['project', 'project name']),
            'customer_name' => $this->findIndex($header, ['customer', 'client']),
            'finder_dept' => $this->findIndex($header, ['finder dept', 'finder department', 'found by dept']),
            'receiver_dept' => $this->findIndex($header, ['receiver dept', 'receiver department', 'issued to dept']),
            'defect_category' => $this->findIndex($header, ['defect category', 'category', 'defect type']),
            'defect_description' => $this->findIndex($header, ['description', 'defect description', 'problem']),
            'severity' => $this->findIndex($header, ['severity', 'level', 'criticality']),
            'disposition' => $this->findIndex($header, ['disposition', 'action']),
            'status' => $this->findIndex($header, ['status', 'state']),
            'created_by' => $this->findIndex($header, ['created by', 'originator', 'author']),
        ];
    }

    private function findIndex($header, $possibleNames)
    {
        foreach ($possibleNames as $name) {
            $index = array_search($name, $header);
            if ($index !== false) {
                return $index;
            }
        }
        return -1; // Not found
    }

    private function processRow($row, $map)
    {
        // Skip if required fields missing
        if ($map['ncr_number'] == -1 || !isset($row[$map['ncr_number']])) {
            $this->warn("Skipping row: NCR Number missing");
            return;
        }

        $ncrNumber = $row[$map['ncr_number']];
        
        // Check duplicate
        if (NCR::where('ncr_number', $ncrNumber)->exists()) {
            $this->warn("Skipping duplicate NCR: {$ncrNumber}");
            return;
        }

        // Get or Create Master Data
        $finderDept = $this->getDepartment($row[$map['finder_dept']] ?? 'Unknown');
        $receiverDept = $this->getDepartment($row[$map['receiver_dept']] ?? 'Unknown');
        $defectCategory = $this->getDefectCategory($row[$map['defect_category']] ?? 'General');
        $severityLevel = $this->getSeverityLevel($row[$map['severity']] ?? 'Minor');
        $dispositionMethod = $this->getDispositionMethod($row[$map['disposition']] ?? 'Rework');
        $creator = $this->getUser($row[$map['created_by']] ?? 'System Migration');

        // Parse Date
        $dateFound = $this->parseDate($row[$map['date_found']] ?? null);

        // Map Status
        $status = $this->mapStatus($row[$map['status']] ?? 'Closed');

        NCR::create([
            'ncr_number' => $ncrNumber,
            'date_found' => $dateFound,
            'order_number' => $row[$map['order_number']] ?? null,
            'project_name' => $row[$map['project_name']] ?? null,
            'customer_name' => $row[$map['customer_name']] ?? null,
            'finder_dept_id' => $finderDept->id,
            'receiver_dept_id' => $receiverDept->id,
            'defect_category_id' => $defectCategory->id,
            'defect_description' => $row[$map['defect_description']] ?? 'Imported from Legacy System',
            'severity_level_id' => $severityLevel->id,
            'disposition_method_id' => $dispositionMethod->id,
            'status' => $status,
            'created_by_user_id' => $creator->id,
            // Defaults for imported closed records
            'closed_at' => $status === 'Closed' ? $dateFound : null, // Approx
            'closed_by_user_id' => $status === 'Closed' ? $creator->id : null,
        ]);
    }

    private function getDepartment($name)
    {
        return Department::firstOrCreate(
            ['department_name' => trim($name)],
            ['department_code' => strtoupper(substr($name, 0, 3)), 'is_active' => true]
        );
    }

    private function getDefectCategory($name)
    {
        return DefectCategory::firstOrCreate(
            ['category_name' => trim($name)],
            ['description' => 'Imported category']
        );
    }

    private function getSeverityLevel($name)
    {
        return SeverityLevel::firstOrCreate(
            ['level_name' => trim($name)],
            ['description' => 'Imported level', 'response_time_hours' => 24]
        );
    }

    private function getDispositionMethod($name)
    {
        return DispositionMethod::firstOrCreate(
            ['method_name' => trim($name)],
            ['description' => 'Imported method', 'requires_customer_approval' => false]
        );
    }

    private function getUser($name)
    {
        // Simple user mapping or creation
        // In reality, might need email or ID mapping
        return User::firstOrCreate(
            ['name' => trim($name)],
            [
                'email' => strtolower(str_replace(' ', '.', trim($name))) . '@example.com',
                'password' => bcrypt('password'), // Default password
                'role_id' => 1, // Default to lowest role, fix later
                'department_id' => 1, // Default dept
                'is_active' => true
            ]
        );
    }

    private function parseDate($dateStr)
    {
        if (!$dateStr) return now();
        try {
            return Carbon::parse($dateStr);
        } catch (\Exception $e) {
            return now();
        }
    }

    private function mapStatus($status)
    {
        $status = strtolower(trim($status));
        return match ($status) {
            'open' => 'Open',
            'closed' => 'Closed',
            'draft' => 'Draft',
            'cancelled' => 'Cancelled',
            default => 'Closed', // Assume legacy data mostly closed
        };
    }
}
