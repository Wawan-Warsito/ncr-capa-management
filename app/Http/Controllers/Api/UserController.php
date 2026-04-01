<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'department']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by Department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by Status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('name')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'required|string|max:50|unique:users',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
                'department_id' => $validated['department_id'],
                'employee_id' => $validated['employee_id'],
                'phone' => $request->phone,
                'is_active' => $request->boolean('is_active', true),
            ]);

            ActivityLog::logActivity(
                'User',
                $user->id,
                'Created',
                "User {$user->name} created",
                null,
                null,
                $currentUser
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user->load(['role', 'department'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->load(['role', 'department'])
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8'
        ]);

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $validated['role_id'],
                'department_id' => $validated['department_id'],
                'employee_id' => $validated['employee_id'],
                'phone' => $request->phone,
                'is_active' => $request->boolean('is_active'),
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            ActivityLog::logActivity(
                'User',
                $user->id,
                'Updated',
                "User {$user->name} updated",
                null,
                null,
                $currentUser
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->load(['role', 'department'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->id === $currentUser->id) {
            return response()->json(['message' => 'Cannot delete yourself'], 400);
        }

        // Check if user has related records that prevent deletion
        if ($user->createdNcrs()->exists() || $user->assignedNcrs()->exists() || $user->assignedCapas()->exists()) {
            return response()->json(['message' => 'Cannot delete user with associated records. Deactivate instead.'], 400);
        }

        DB::beginTransaction();
        try {
            $userName = $user->name;
            $user->delete();

            ActivityLog::logActivity(
                'User',
                $user->id, // ID might not be valid after delete for reference, but usually kept in logs or null
                'Deleted',
                "User {$userName} deleted",
                null,
                null,
                $currentUser
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user password (Admin only)
     */
    public function resetPassword(Request $request, User $user)
    {
        $currentUser = $request->user();
        if (!$currentUser->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        ActivityLog::logActivity(
            'User',
            $user->id,
            'Password_Reset',
            "Password reset by admin for {$user->email}",
            null,
            null,
            $currentUser
        );

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset',
        ]);
    }
}
