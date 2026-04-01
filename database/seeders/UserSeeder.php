<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        $password = Hash::make('password');

        // Helper to get ID
        $getRoleId = fn($name) => Role::where('role_name', $name)->value('id');
        $getDeptId = fn($code) => Department::where('department_code', $code)->value('id');

        $users = [
            // 1. Administrator
            [
                'name' => 'Administrator',
                'email' => 'admin.ncr@tab-indonesia.co.id',
                'role_id' => $getRoleId('Super Admin'),
                'department_id' => $getDeptId('IT'),
                'employee_id' => 'ADMIN001',
            ],
            // 2. QC Manager
            [
                'name' => 'Wahono Adisuranto',
                'email' => 'qc.manager@tab-indonesia.co.id',
                'role_id' => $getRoleId('QC Manager'),
                'department_id' => $getDeptId('QC'),
                'employee_id' => 'QC001',
            ],
            // 3. Production Manager
            [
                'name' => 'Muh Zein Destiawan',
                'email' => 'prod.manager@tab-indonesia.co.id',
                'role_id' => $getRoleId('Department Manager'),
                'department_id' => $getDeptId('PROD'),
                'employee_id' => 'PROD001',
            ],
            // 4. Production Staff (User)
            [
                'name' => 'Felita Cindy Veronika',
                'email' => 'prod.staff@tab-indonesia.co.id',
                'role_id' => $getRoleId('User'),
                'department_id' => $getDeptId('PROD'),
                'employee_id' => 'PROD002',
            ],
            // 5. QC Staff 1 (User)
            [
                'name' => 'Wawan Warsito',
                'email' => 'qc.staff1@tab-indonesia.co.id',
                'role_id' => $getRoleId('User'),
                'department_id' => $getDeptId('QC'),
                'employee_id' => 'QC002',
            ],
            // 6. QC Engineer (User/Engineer role if exists, mapping to User for now)
            [
                'name' => 'Ari Febrianto',
                'email' => 'qc.engineer@tab-indonesia.co.id',
                'role_id' => $getRoleId('User'),
                'department_id' => $getDeptId('QC'),
                'employee_id' => 'QC003',
            ],
            // 7. QC Inspector 1
            [
                'name' => 'Muchsin',
                'email' => 'qc.inspector1@tab-indonesia.co.id',
                'role_id' => $getRoleId('User'),
                'department_id' => $getDeptId('QC'),
                'employee_id' => 'QC004',
            ],
            // 8. QC Inspector 2
            [
                'name' => 'Prababta Pirosta Maharaya',
                'email' => 'qc.inspector2@tab-indonesia.co.id',
                'role_id' => $getRoleId('User'),
                'department_id' => $getDeptId('QC'),
                'employee_id' => 'QC005',
            ],
            // 9. QC Staff 2
            [
                'name' => 'Iwan',
                'email' => 'qc.staff2@tab-indonesia.co.id',
                'role_id' => $getRoleId('User'),
                'department_id' => $getDeptId('QC'),
                'employee_id' => 'QC006',
            ],
            // 10. Production Supervisor (User/Dept Manager? Mapping to User for now unless spv role exists)
            [
                'name' => 'Supraptiwo',
                'email' => 'prod.spv@tab-indonesia.co.id',
                'role_id' => $getRoleId('User'),
                'department_id' => $getDeptId('PROD'),
                'employee_id' => 'PROD003',
            ],
            // 11. Design Manager (Engineering)
            [
                'name' => 'Satrio Raharjo',
                'email' => 'design.manager@tab-indonesia.co.id',
                'role_id' => $getRoleId('Department Manager'),
                'department_id' => $getDeptId('ENG'),
                'employee_id' => 'ENG001',
            ],
            // 12. PSD Manager (SCM)
            [
                'name' => 'Andi Suwandi',
                'email' => 'psd.manager@tab-indonesia.co.id',
                'role_id' => $getRoleId('Department Manager'),
                'department_id' => $getDeptId('SCM'),
                'employee_id' => 'SCM001',
            ],
            // 13. Procurement Manager (SCM)
            [
                'name' => 'Agung Yulianto',
                'email' => 'proc.manager@tab-indonesia.co.id',
                'role_id' => $getRoleId('Department Manager'),
                'department_id' => $getDeptId('SCM'),
                'employee_id' => 'SCM002',
            ],
            // 14. Warehouse Staff 1 (SCM)
            [
                'name' => 'Irpan',
                'email' => 'wh.staff1@tab-indonesia.co.id',
                'role_id' => $getRoleId('User'),
                'department_id' => $getDeptId('SCM'),
                'employee_id' => 'SCM003',
            ],
            // 15. EXIM (SCM)
            [
                'name' => 'Ridwan Ferdiansyah',
                'email' => 'exim@tab-indonesia.co.id',
                'role_id' => $getRoleId('User'),
                'department_id' => $getDeptId('SCM'),
                'employee_id' => 'SCM004',
            ],
            // 16. Project Manager 1 (Engineering)
            [
                'name' => 'Rizky Kurniawan',
                'email' => 'project.manager1@tab-indonesia.co.id',
                'role_id' => $getRoleId('Department Manager'),
                'department_id' => $getDeptId('ENG'),
                'employee_id' => 'ENG002',
            ],
            // 17. Project Manager 2 (Engineering)
            [
                'name' => 'Agus Yulianto',
                'email' => 'project.manager2@tab-indonesia.co.id',
                'role_id' => $getRoleId('Department Manager'),
                'department_id' => $getDeptId('ENG'),
                'employee_id' => 'ENG003',
            ],
            // 18. Purchasing Specialist (SCM)
            [
                'name' => 'Andri',
                'email' => 'purch.specialist@tab-indonesia.co.id',
                'role_id' => $getRoleId('User'),
                'department_id' => $getDeptId('SCM'),
                'employee_id' => 'SCM005',
            ],
        ];

        foreach ($users as $userData) {
            User::create(array_merge($userData, [
                'password' => $password,
                'is_active' => true,
                'phone' => '08123456789', // Default placeholder
            ]));
        }
    }
}
