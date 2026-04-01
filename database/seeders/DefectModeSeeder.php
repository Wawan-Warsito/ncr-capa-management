<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\DefectCategory;

class DefectModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define Defect Modes and their Groups (Categories)
        $data = [
            // None
            ['group' => 'None', 'name' => 'NCR', 'description' => 'None - NCR'],

            // Manuf.
            ['group' => 'Manuf.', 'name' => 'Bent', 'description' => 'Manuf. - Bent'],
            ['group' => 'Manuf.', 'name' => 'Blunt / Not Sharp', 'description' => 'Manuf. - Blunt / Not Sharp'],
            ['group' => 'Manuf.', 'name' => 'Broken into pieces, Separated, Detached', 'description' => 'Manuf. - Broken into pieces, Separated, Detached'],
            ['group' => 'Manuf.', 'name' => 'Burnt / Melted', 'description' => 'Manuf. - Burnt / Melted'],
            ['group' => 'Manuf.', 'name' => 'Burr / Sharp edge', 'description' => 'Manuf. - Burr / Sharp edge'],
            ['group' => 'Manuf.', 'name' => 'Certificate Out of Date / Expired', 'description' => 'Manuf. - Certificate Out of Date / Expired'],
            ['group' => 'Manuf.', 'name' => 'Cracked', 'description' => 'Manuf. - Cracked'],
            ['group' => 'Manuf.', 'name' => 'Cylindrical defect (Uneven radius)', 'description' => 'Manuf. - Cylindrical defect (Uneven radius)'],
            ['group' => 'Manuf.', 'name' => 'Damage', 'description' => 'Manuf. - Damage'],
            ['group' => 'Manuf.', 'name' => 'Deformation', 'description' => 'Manuf. - Deformation'],
            ['group' => 'Manuf.', 'name' => 'Delivery Note Error/Missing', 'description' => 'Manuf. - Delivery Note Error/Missing'],
            ['group' => 'Manuf.', 'name' => 'Dented, Tools marks, damaged surface', 'description' => 'Manuf. - Dented, Tools marks, damaged surface'],
            ['group' => 'Manuf.', 'name' => 'Dimension Out of Tolerance', 'description' => 'Manuf. - Dimension Out of Tolerance'],
            ['group' => 'Manuf.', 'name' => 'Discoloration / Temper Color', 'description' => 'Manuf. - Discoloration / Temper Color'],
            ['group' => 'Manuf.', 'name' => 'Fabricated not according drawing', 'description' => 'Manuf. - Fabricated not according drawing'],
            ['group' => 'Manuf.', 'name' => 'Incomplete / Missing Material / Part', 'description' => 'Manuf. - Incomplete / Missing Material / Part'],
            ['group' => 'Manuf.', 'name' => 'Incomplete Machining', 'description' => 'Manuf. - Incomplete Machining'],
            ['group' => 'Manuf.', 'name' => 'Incorrect Orientation / Location', 'description' => 'Manuf. - Incorrect Orientation / Location'],
            ['group' => 'Manuf.', 'name' => 'Incorrect raw material', 'description' => 'Manuf. - Incorrect raw material'],
            ['group' => 'Manuf.', 'name' => 'Incorrect Thread', 'description' => 'Manuf. - Incorrect Thread'],
            ['group' => 'Manuf.', 'name' => 'Installation incorrect Angle', 'description' => 'Manuf. - Installation incorrect Angle'],
            ['group' => 'Manuf.', 'name' => 'Lack of updating drawing / instruction', 'description' => 'Manuf. - Lack of updating drawing / instruction'],
            ['group' => 'Manuf.', 'name' => 'Leakage', 'description' => 'Manuf. - Leakage'],
            ['group' => 'Manuf.', 'name' => 'Loose, Disconnected, Untight, Not connected', 'description' => 'Manuf. - Loose, Disconnected, Untight, Not connected'],
            ['group' => 'Manuf.', 'name' => 'Material Defect', 'description' => 'Manuf. - Material Defect'],
            ['group' => 'Manuf.', 'name' => 'Minimum thickness out of specification', 'description' => 'Manuf. - Minimum thickness out of specification'],
            ['group' => 'Manuf.', 'name' => 'Missing marking', 'description' => 'Manuf. - Missing marking'],
            ['group' => 'Manuf.', 'name' => 'Missing Part', 'description' => 'Manuf. - Missing Part'],
            ['group' => 'Manuf.', 'name' => 'Not concentric', 'description' => 'Manuf. - Not concentric'],
            ['group' => 'Manuf.', 'name' => 'Not follow IFM drawing', 'description' => 'Manuf. - Not follow IFM drawing'],
            ['group' => 'Manuf.', 'name' => 'Not follow NDT requirement', 'description' => 'Manuf. - Not follow NDT requirement'],
            ['group' => 'Manuf.', 'name' => 'Notch, indentation on edge or surface', 'description' => 'Manuf. - Notch, indentation on edge or surface'],
            ['group' => 'Manuf.', 'name' => 'Out of performance / Incorrect function', 'description' => 'Manuf. - Out of performance / Incorrect function'],
            ['group' => 'Manuf.', 'name' => 'Parallellity defect', 'description' => 'Manuf. - Parallellity defect'],
            ['group' => 'Manuf.', 'name' => 'Rusty / Corroded', 'description' => 'Manuf. - Rusty / Corroded'],
            ['group' => 'Manuf.', 'name' => 'Scratched', 'description' => 'Manuf. - Scratched'],
            ['group' => 'Manuf.', 'name' => 'Shape Deviation out of Tolerance', 'description' => 'Manuf. - Shape Deviation out of Tolerance'],
            ['group' => 'Manuf.', 'name' => 'Slope / Inclination out of tolerance', 'description' => 'Manuf. - Slope / Inclination out of tolerance'],
            ['group' => 'Manuf.', 'name' => 'Stuck, Blocked', 'description' => 'Manuf. - Stuck, Blocked'],
            ['group' => 'Manuf.', 'name' => 'Surface contamination', 'description' => 'Manuf. - Surface contamination'],
            ['group' => 'Manuf.', 'name' => 'Surface Treatment Wrong / Error', 'description' => 'Manuf. - Surface Treatment Wrong / Error'],
            ['group' => 'Manuf.', 'name' => 'Twisted', 'description' => 'Manuf. - Twisted'],
            ['group' => 'Manuf.', 'name' => 'Unclean (chips, dust, polluted, greasy, oil)', 'description' => 'Manuf. - Unclean (chips, dust, polluted, greasy, oil)'],
            ['group' => 'Manuf.', 'name' => 'Wavy', 'description' => 'Manuf. - Wavy'],
            ['group' => 'Manuf.', 'name' => 'Worn, erroded', 'description' => 'Manuf. - Worn, erroded'],
            ['group' => 'Manuf.', 'name' => 'Wrong location', 'description' => 'Manuf. - Wrong location'],
            ['group' => 'Manuf.', 'name' => 'Wrong marking', 'description' => 'Manuf. - Wrong marking'],
            ['group' => 'Manuf.', 'name' => 'Wrong Material Specification', 'description' => 'Manuf. - Wrong Material Specification'],
            ['group' => 'Manuf.', 'name' => 'Wrong part no according to Packilist', 'description' => 'Manuf. - Wrong part no according to Packilist'],
            ['group' => 'Manuf.', 'name' => 'Wrong Quantity', 'description' => 'Manuf. - Wrong Quantity'],
            ['group' => 'Manuf.', 'name' => 'Wrong Size', 'description' => 'Manuf. - Wrong Size'],
            ['group' => 'Manuf.', 'name' => 'Wrong Specification of Material', 'description' => 'Manuf. - Wrong Specification of Material'],

            // Welding
            ['group' => 'Welding', 'name' => 'BT - Burn through', 'description' => 'Welding - BT - Burn through'],
            ['group' => 'Welding', 'name' => 'CR - Crack', 'description' => 'Welding - CR - Crack'],
            ['group' => 'Welding', 'name' => 'Deformation / Distortion', 'description' => 'Welding - Deformation / Distortion'],
            ['group' => 'Welding', 'name' => 'IF/LOF - Lack of fusion', 'description' => 'Welding - IF/LOF - Lack of fusion'],
            ['group' => 'Welding', 'name' => 'Insufficient throat thickness', 'description' => 'Welding - Insufficient throat thickness'],
            ['group' => 'Welding', 'name' => 'IP - Incomplete root penetration', 'description' => 'Welding - IP - Incomplete root penetration'],
            ['group' => 'Welding', 'name' => 'Misalignment', 'description' => 'Welding - Misalignment'],
            ['group' => 'Welding', 'name' => 'Porosity / pinhole', 'description' => 'Welding - Porosity / pinhole'],
            ['group' => 'Welding', 'name' => 'RC - Root concavity', 'description' => 'Welding - RC - Root concavity'],
            ['group' => 'Welding', 'name' => 'Sagging / Incompletely filled groove', 'description' => 'Welding - Sagging / Incompletely filled groove'],
            ['group' => 'Welding', 'name' => 'SI - Slag Inclussion', 'description' => 'Welding - SI - Slag Inclussion'],
            ['group' => 'Welding', 'name' => 'SPT - Spatter', 'description' => 'Welding - SPT - Spatter'],
            ['group' => 'Welding', 'name' => 'TC - Temper colour (Discolouration)', 'description' => 'Welding - TC - Temper colour (Discolouration)'],
            ['group' => 'Welding', 'name' => 'TI - Tungsten Inclusion', 'description' => 'Welding - TI - Tungsten Inclusion'],
            ['group' => 'Welding', 'name' => 'U/C - Undercut', 'description' => 'Welding - U/C - Undercut'],
            ['group' => 'Welding', 'name' => 'U/F - Underfill', 'description' => 'Welding - U/F - Underfill'],
            ['group' => 'Welding', 'name' => 'Wrong Fit-up', 'description' => 'Welding - Wrong Fit-up'],

            // Purch
            ['group' => 'Purch', 'name' => 'Burr / Sharp edge', 'description' => 'Purch - Burr / Sharp edge'],
            ['group' => 'Purch', 'name' => 'Cracked', 'description' => 'Purch - Cracked'],
            ['group' => 'Purch', 'name' => 'Damaged', 'description' => 'Purch - Damaged'],
            ['group' => 'Purch', 'name' => 'Delivery Note Error/Missing', 'description' => 'Purch - Delivery Note Error/Missing'],
            ['group' => 'Purch', 'name' => 'Deviation Permit Error/Missing', 'description' => 'Purch - Deviation Permit Error/Missing'],
            ['group' => 'Purch', 'name' => 'Dimension Out of Tolerance', 'description' => 'Purch - Dimension Out of Tolerance'],
            ['group' => 'Purch', 'name' => 'Dimension Report Error/Missing', 'description' => 'Purch - Dimension Report Error/Missing'],
            ['group' => 'Purch', 'name' => 'Document Out of Date / expired', 'description' => 'Purch - Document Out of Date / expired'],
            ['group' => 'Purch', 'name' => 'Electrical Wrong setting', 'description' => 'Purch - Electrical Wrong setting'],
            ['group' => 'Purch', 'name' => 'Fabricated not according drawing', 'description' => 'Purch - Fabricated not according drawing'],
            ['group' => 'Purch', 'name' => 'Incorrect Thread', 'description' => 'Purch - Incorrect Thread'],
            ['group' => 'Purch', 'name' => 'Installation Doc. Error/Missing', 'description' => 'Purch - Installation Doc. Error/Missing'],
            ['group' => 'Purch', 'name' => 'Loading / unloading error', 'description' => 'Purch - Loading / unloading error'],
            ['group' => 'Purch', 'name' => 'Material Defect/Damage', 'description' => 'Purch - Material Defect/Damage'],
            ['group' => 'Purch', 'name' => 'Minimum thickness out of Tolerance', 'description' => 'Purch - Minimum thickness out of Tolerance'],
            ['group' => 'Purch', 'name' => 'Missing marking', 'description' => 'Purch - Missing marking'],
            ['group' => 'Purch', 'name' => 'Missing part', 'description' => 'Purch - Missing part'],
            ['group' => 'Purch', 'name' => 'MTC is not according to specification', 'description' => 'Purch - MTC is not according to specification'],
            ['group' => 'Purch', 'name' => 'NDT Report Error/Missing', 'description' => 'Purch - NDT Report Error/Missing'],
            ['group' => 'Purch', 'name' => 'Not According to Manual / Procedure', 'description' => 'Purch - Not According to Manual / Procedure'],
            ['group' => 'Purch', 'name' => 'Out of Packing Specification', 'description' => 'Purch - Out of Packing Specification'],
            ['group' => 'Purch', 'name' => 'Oxidised/Corroded', 'description' => 'Purch - Oxidised/Corroded'],
            ['group' => 'Purch', 'name' => 'PV Doc. Error/Missing', 'description' => 'Purch - PV Doc. Error/Missing'],
            ['group' => 'Purch', 'name' => 'Scratched', 'description' => 'Purch - Scratched'],
            ['group' => 'Purch', 'name' => 'Separated', 'description' => 'Purch - Separated'],
            ['group' => 'Purch', 'name' => 'Shape Deviation out of Tolerance', 'description' => 'Purch - Shape Deviation out of Tolerance'],
            ['group' => 'Purch', 'name' => 'Testing Doc, Error/Missing', 'description' => 'Purch - Testing Doc, Error/Missing'],
            ['group' => 'Purch', 'name' => 'Unclean (chips, dust, polluted, greasy, oil)', 'description' => 'Purch - Unclean (chips, dust, polluted, greasy, oil)'],
            ['group' => 'Purch', 'name' => 'Wavy', 'description' => 'Purch - Wavy'],
            ['group' => 'Purch', 'name' => 'Welding - U/C - Undercut', 'description' => 'Purch - Welding - U/C - Undercut'],
            ['group' => 'Purch', 'name' => 'Welding Doc. Error/Missing', 'description' => 'Purch - Welding Doc. Error/Missing'],
            ['group' => 'Purch', 'name' => 'Wrong Info:  Surface finish', 'description' => 'Purch - Wrong Info:  Surface finish'],
            ['group' => 'Purch', 'name' => 'Wrong marking', 'description' => 'Purch - Wrong marking'],
            ['group' => 'Purch', 'name' => 'Wrong part', 'description' => 'Purch - Wrong part'],
            ['group' => 'Purch', 'name' => 'Wrong quantity', 'description' => 'Purch - Wrong quantity'],
            ['group' => 'Purch', 'name' => 'Wrong Size / Dimension', 'description' => 'Purch - Wrong Size / Dimension'],
            ['group' => 'Purch', 'name' => 'Wrong Specification of Material', 'description' => 'Purch - Wrong Specification of Material'],

            // Doc (Documentation)
            ['group' => 'Documentation', 'name' => 'Installation Doc. Error/Missing', 'description' => 'Doc - Installation Doc. Error/Missing'],
            ['group' => 'Documentation', 'name' => 'Dimension Report Error/Missing', 'description' => 'Doc - Dimension Report Error/Missing'],
            ['group' => 'Documentation', 'name' => 'MTR Error/Missing', 'description' => 'Doc - MTR Error/Missing'],
            ['group' => 'Documentation', 'name' => 'NDT Report Error/Missing', 'description' => 'Doc - NDT Report Error/Missing'],
            ['group' => 'Documentation', 'name' => 'Not According to Manual / Procedure', 'description' => 'Doc - Not According to Manual / Procedure'],
            ['group' => 'Documentation', 'name' => 'PV Doc. Error/Missing', 'description' => 'Doc - PV Doc. Error/Missing'],
            ['group' => 'Documentation', 'name' => 'Test Doc, Error/Missing', 'description' => 'Doc - Test Doc, Error/Missing'],
            ['group' => 'Documentation', 'name' => 'Welding Doc. Error/Missing', 'description' => 'Doc - Welding Doc. Error/Missing'],

            // Dsg.
            ['group' => 'Dsg.', 'name' => 'Automation Non-functional design', 'description' => 'Dsg. - Automation Non-functional design'],
            ['group' => 'Dsg.', 'name' => 'Difficult to Maintain', 'description' => 'Dsg. - Difficult to Maintain'],
            ['group' => 'Dsg.', 'name' => 'Difficult to Manufacture', 'description' => 'Dsg. - Difficult to Manufacture'],
            ['group' => 'Dsg.', 'name' => 'Difficult to Operate', 'description' => 'Dsg. - Difficult to Operate'],
            ['group' => 'Dsg.', 'name' => 'Electrical Non-functional design', 'description' => 'Dsg. - Electrical Non-functional design'],
            ['group' => 'Dsg.', 'name' => 'Mech. Calculation Error', 'description' => 'Dsg. - Mech. Calculation Error'],
            ['group' => 'Dsg.', 'name' => 'Mechanical Non-functional design', 'description' => 'Dsg. - Mechanical Non-functional design'],
            ['group' => 'Dsg.', 'name' => 'Mismatching issued drawing', 'description' => 'Dsg. - Mismatching issued drawing'],
            ['group' => 'Dsg.', 'name' => 'Miss Info:  Bill of material', 'description' => 'Dsg. - Miss Info:  Bill of material'],
            ['group' => 'Dsg.', 'name' => 'Miss Info:  Dimension', 'description' => 'Dsg. - Miss Info:  Dimension'],
            ['group' => 'Dsg.', 'name' => 'Miss Info:  Material', 'description' => 'Dsg. - Miss Info:  Material'],
            ['group' => 'Dsg.', 'name' => 'Miss Info:  Notes', 'description' => 'Dsg. - Miss Info:  Notes'],
            ['group' => 'Dsg.', 'name' => 'Miss Info:  Surface finish', 'description' => 'Dsg. - Miss Info:  Surface finish'],
            ['group' => 'Dsg.', 'name' => 'Miss Info:  Tolerence', 'description' => 'Dsg. - Miss Info:  Tolerence'],
            ['group' => 'Dsg.', 'name' => 'Miss Info:  Welds', 'description' => 'Dsg. - Miss Info:  Welds'],
            ['group' => 'Dsg.', 'name' => 'Miss Info: Geometry', 'description' => 'Dsg. - Miss Info: Geometry'],
            ['group' => 'Dsg.', 'name' => 'Missing drawing', 'description' => 'Dsg. - Missing drawing'],
            ['group' => 'Dsg.', 'name' => 'Not Hygienically Designed', 'description' => 'Dsg. - Not Hygienically Designed'],
            ['group' => 'Dsg.', 'name' => 'Software Error', 'description' => 'Dsg. - Software Error'],
            ['group' => 'Dsg.', 'name' => 'Specification Unclear', 'description' => 'Dsg. - Specification Unclear'],
            ['group' => 'Dsg.', 'name' => 'Wrong Design:  Not according to Code', 'description' => 'Dsg. - Wrong Design:  Not according to Code'],
            ['group' => 'Dsg.', 'name' => 'Wrong Info:  Bill of material', 'description' => 'Dsg. - Wrong Info:  Bill of material'],
            ['group' => 'Dsg.', 'name' => 'Wrong Info:  Dimension', 'description' => 'Dsg. - Wrong Info:  Dimension'],
            ['group' => 'Dsg.', 'name' => 'Wrong Info:  Geometry', 'description' => 'Dsg. - Wrong Info:  Geometry'],
            ['group' => 'Dsg.', 'name' => 'Wrong Info:  Material', 'description' => 'Dsg. - Wrong Info:  Material'],
            ['group' => 'Dsg.', 'name' => 'Wrong Info:  Notes', 'description' => 'Dsg. - Wrong Info:  Notes'],
            ['group' => 'Dsg.', 'name' => 'Wrong Info:  Specification', 'description' => 'Dsg. - Wrong Info:  Specification'],
            ['group' => 'Dsg.', 'name' => 'Wrong Info:  Surface finish', 'description' => 'Dsg. - Wrong Info:  Surface finish'],
            ['group' => 'Dsg.', 'name' => 'Wrong Info:  Testing', 'description' => 'Dsg. - Wrong Info:  Testing'],
            ['group' => 'Dsg.', 'name' => 'Wrong Info:  Tolerence', 'description' => 'Dsg. - Wrong Info:  Tolerence'],
            ['group' => 'Dsg.', 'name' => 'Wrong Info:  Welds', 'description' => 'Dsg. - Wrong Info:  Welds'],

            // Elect
            ['group' => 'Elect', 'name' => 'Incorrect function', 'description' => 'Elect - Incorrect function'],
            ['group' => 'Elect', 'name' => 'Incorrect marking or label', 'description' => 'Elect - Incorrect marking or label'],
            ['group' => 'Elect', 'name' => 'Missing marking or label', 'description' => 'Elect - Missing marking or label'],
            ['group' => 'Elect', 'name' => 'Signal error, Software error', 'description' => 'Elect - Signal error, Software error'],
            ['group' => 'Elect', 'name' => 'Stops functioning', 'description' => 'Elect - Stops functioning'],
            ['group' => 'Elect', 'name' => 'Wrong setting', 'description' => 'Elect - Wrong setting'],

            // Additional Generic Modes for other Groups
            ['group' => 'Material Defect', 'name' => 'General Material Defect', 'description' => 'General defect for Material'],
            ['group' => 'Process Deviation', 'name' => 'General Process Deviation', 'description' => 'General deviation for Process'],
            ['group' => 'Equipment Failure', 'name' => 'General Equipment Failure', 'description' => 'General failure for Equipment'],
            ['group' => 'Human Error', 'name' => 'General Human Error', 'description' => 'General Human Error'],
            
            ['group' => 'Assembling', 'name' => 'General Assembling Defect', 'description' => 'General defect for Assembling'],
            ['group' => 'External', 'name' => 'General External Issue', 'description' => 'General issue for External'],
            ['group' => 'Internal', 'name' => 'General Internal Issue', 'description' => 'General issue for Internal'],
            ['group' => 'Preparation', 'name' => 'General Preparation Issue', 'description' => 'General issue for Preparation'],
            ['group' => 'Subcont', 'name' => 'General Subcontractor Issue', 'description' => 'General issue for Subcontractor'],
            ['group' => 'Supplier', 'name' => 'General Supplier Issue', 'description' => 'General issue for Supplier'],
            ['group' => 'Testing', 'name' => 'General Testing Issue', 'description' => 'General issue for Testing'],
        ];

        // 1. Ensure Defect Groups (Categories) exist
        $groups = array_unique(array_column($data, 'group'));
        foreach ($groups as $groupName) {
            $code = strtoupper(substr($groupName, 0, 5));
            // Check if code exists to avoid duplicate entry error
            if (!DefectCategory::where('category_code', $code)->exists()) {
                DefectCategory::firstOrCreate(
                    ['category_name' => $groupName],
                    [
                        'category_code' => $code, 
                        'description' => "Category for $groupName",
                        'is_active' => true
                    ]
                );
            }
        }

        // 2. Insert Defect Modes
        foreach ($data as $item) {
            $category = DefectCategory::where('category_name', $item['group'])->first();
            
            if ($category) {
                DB::table('defect_modes')->updateOrInsert(
                    [
                        'defect_category_id' => $category->id,
                        'mode_name' => $item['name']
                    ],
                    [
                        'mode_description' => $item['description'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}
