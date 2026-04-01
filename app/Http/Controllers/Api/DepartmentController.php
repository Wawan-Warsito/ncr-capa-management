<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index(Request $request)
    {
        $query = Department::with('manager');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('department_name', 'like', "%{$search}%")
                  ->orWhere('department_code', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Allow fetching all without pagination for dropdowns
        if ($request->has('all')) {
            $departments = $query->orderBy('department_name')->get();
        } else {
            $departments = $query->orderBy('department_name')->paginate(10);
        }

        return response()->json([
            'success' => true,
            'data' => $departments
        ]);
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'department_name' => 'required|string|max:255|unique:departments',
            'department_code' => 'required|string|max:10|unique:departments',
            'manager_user_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $department = Department::create($validated);

            ActivityLog::logActivity(
                'Department',
                $department->id,
                'Created',
                "Department {$department->department_name} created",
                null,
                null,
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully',
                'data' => $department->load('manager')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create department: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        return response()->json([
            'success' => true,
            'data' => $department->load('manager')
        ]);
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'department_name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'department_code' => ['required', 'string', 'max:10', Rule::unique('departments')->ignore($department->id)],
            'manager_user_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $department->update($validated);

            ActivityLog::logActivity(
                'Department',
                $department->id,
                'Updated',
                "Department {$department->department_name} updated",
                null,
                null,
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully',
                'data' => $department->load('manager')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update department: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Request $request, Department $department)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check dependencies
        if ($department->users()->exists()) {
            return response()->json(['message' => 'Cannot delete department with associated users.'], 400);
        }
        if ($department->finderNcrs()->exists() || $department->receiverNcrs()->exists()) {
            return response()->json(['message' => 'Cannot delete department with associated NCRs.'], 400);
        }

        DB::beginTransaction();
        try {
            $name = $department->department_name;
            $department->delete();

            ActivityLog::logActivity(
                'Department',
                $department->id,
                'Deleted',
                "Department {$name} deleted",
                null,
                null,
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department: ' . $e->getMessage()
            ], 500);
        }
    }
}
