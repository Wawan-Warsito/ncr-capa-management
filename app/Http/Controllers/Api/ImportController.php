<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\NCRImport;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function downloadNcrImportTemplate()
    {
        $columns = [
            'ncr_no',
            'line_no',
            'date_found',
            'issued_date',
            'finder_dept',
            'receiver_dept',
            'finder',
            'finder_manager',
            'project_name',
            'project_sn',
            'part_name',
            'order_no',
            'po_no',
            'customer',
            'dwg_doc_no',
            'defect_area',
            'subcont_supplier_name',
            'defect_group',
            'defect_mode',
            'defect_description',
            'severity',
            'disposition',
            'corrective_action',
            'assigned_pic',
            'receiver_comments',
            'mh_used',
            'mh_rate',
            'labor_cost',
            'material_cost',
            'subcont_cost',
            'engineering_cost',
            'other_cost',
            'total_cost',
            'root_cause',
            'preventive_action',
            'evaluation_of_effectiveness',
            'ca_finish_date',
            'status',
        ];

        $sample = [
            '25.P00-QC-01',
            '1',
            '2026-03-04',
            '2026-03-04',
            'QC',
            'PROD',
            'Muchsin',
            'Wahono Adisuranto',
            'Project ABC',
            '25.P00-ABC-01',
            'Jacket Shell',
            'PO25-001125',
            'PO25-001125',
            'Santen Pharmaceutical',
            'D0200002357',
            'Workshop',
            'Mitra Teguh Steel',
            'Welding',
            'RC - Root concavity',
            'Terjadi defect welding pada area jacket shell root concavity',
            'Major',
            'Repaired',
            'Containment done',
            '',
            '',
            '12',
            '1',
            '12',
            '2',
            '0',
            '0',
            '0',
            '20',
            'Root cause text',
            'Preventive action text',
            'Preventive action verified no more same issue after 3 months period',
            '2026-03-20',
            'Draft',
        ];

        $fileName = 'ncr_import_template_v2_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($columns, $sample) {
            $out = fopen('php://output', 'w');
            fwrite($out, "sep=,\r\n");
            fputcsv($out, $columns);
            fputcsv($out, $sample);
            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function importNCR(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        try {
            $file = $request->file('file');
            $mime = $file->getClientMimeType();
            $ext = strtolower($file->getClientOriginalExtension());
            $allowedMimes = [
                'text/csv',
                'text/plain',
                'application/csv',
                'application/vnd.ms-excel',
                'application/octet-stream',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];
            $allowedExts = ['csv', 'xlsx', 'xls'];
            if (!in_array($mime, $allowedMimes) && !in_array($ext, $allowedExts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported file type. Please upload CSV/XLS/XLSX.',
                ], 422);
            }
            
            // 1. Read the file as array to find the header row
            $data = Excel::toArray([], $file);
            $sheet = $data[0] ?? [];
            
            $headerRowIndex = 1; // Default
            $found = false;

            // Look for a row containing "NCR" or "No" or "Date"
            foreach ($sheet as $index => $row) {
                // Convert to string and lowercase for comparison
                $rowStr = implode(' ', array_map(fn($item) => strtolower((string)$item), $row));
                
                if (str_contains($rowStr, 'ncr') && (str_contains($rowStr, 'no') || str_contains($rowStr, 'date'))) {
                    $headerRowIndex = $index + 1; // Excel rows are 1-based
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // Fallback: Check for just 'ncr'
                foreach ($sheet as $index => $row) {
                    $rowStr = implode(' ', array_map(fn($item) => strtolower((string)$item), $row));
                    if (str_contains($rowStr, 'ncr')) {
                        $headerRowIndex = $index + 1;
                        break;
                    }
                }
            }

            Log::info("Detected header row at: $headerRowIndex");

            // 2. Import using the detected header row
            Excel::import(new NCRImport($headerRowIndex), $file);
            
            $count = \App\Models\NCR::count(); // Check if count increased (rough check)

            return response()->json([
                'success' => true,
                'message' => "NCR data imported successfully. (Header detected at row $headerRowIndex)",
                'debug_count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Import Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to import data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $file = $request->file('file');
            Excel::import(new UsersImport, $file);

            return response()->json([
                'success' => true,
                'message' => 'Users imported successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Import Users Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to import users: ' . $e->getMessage(),
            ], 500);
        }
    }
}
