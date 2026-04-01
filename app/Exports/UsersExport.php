<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::with(['role', 'department'])
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return [
                    'Name' => $u->name,
                    'Email' => $u->email,
                    'Employee ID' => $u->employee_id,
                    'Role' => $u->role ? $u->role->role_name : '',
                    'Department' => $u->department ? $u->department->department_name : '',
                    'Active' => $u->is_active ? 'Yes' : 'No',
                    'Phone' => $u->phone ?? '',
                ];
            });
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Employee ID', 'Role', 'Department', 'Active', 'Phone'];
    }
}
