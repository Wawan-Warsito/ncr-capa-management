<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Normalize headings
                $name = trim((string)($row['name'] ?? ''));
                $email = strtolower(trim((string)($row['email'] ?? '')));
                $employeeId = trim((string)($row['employee_id'] ?? ''));
                $roleName = trim((string)($row['role'] ?? ''));
                $departmentName = trim((string)($row['department'] ?? ''));
                $activeStr = strtolower(trim((string)($row['active'] ?? 'yes')));
                $phone = trim((string)($row['phone'] ?? ''));

                if (!$email || !$name) {
                    continue;
                }

                // Resolve role
                $role = null;
                if ($roleName) {
                    $role = Role::whereRaw('LOWER(role_name) = ?', [strtolower($roleName)])
                        ->orWhereRaw('LOWER(display_name) = ?', [strtolower($roleName)])
                        ->first();
                }

                // Resolve department by name or code
                $department = null;
                if ($departmentName) {
                    $department = Department::whereRaw('LOWER(department_name) = ?', [strtolower($departmentName)])
                        ->orWhereRaw('LOWER(department_code) = ?', [strtolower($departmentName)])
                        ->first();
                }

                $isActive = in_array($activeStr, ['yes', 'y', 'true', '1']);

                // Upsert by email
                $user = User::where('email', $email)->first();
                if ($user) {
                    $user->update([
                        'name' => $name,
                        'employee_id' => $employeeId ?: $user->employee_id,
                        'role_id' => $role ? $role->id : $user->role_id,
                        'department_id' => $department ? $department->id : $user->department_id,
                        'phone' => $phone ?: $user->phone,
                        'is_active' => $isActive,
                    ]);
                } else {
                    // Create with default password if not provided
                    User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'employee_id' => $employeeId ?: null,
                        'role_id' => $role ? $role->id : null,
                        'department_id' => $department ? $department->id : null,
                        'phone' => $phone ?: null,
                        'is_active' => $isActive,
                    ]);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
