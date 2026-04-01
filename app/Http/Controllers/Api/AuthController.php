<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $user = User::where('email', $request->email)->first();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection error.',
            ], 503);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        // Login user to establish session (for SPA)
        Auth::login($user);

        // Update last login
        $user->updateLastLogin();

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Log activity
        ActivityLog::logActivity(
            'User',
            $user->id,
            'Login',
            'User logged in successfully',
            null,
            null,
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->role_name,
                    'role_display' => $user->role->display_name,
                    'department' => $user->department->department_name,
                    'department_code' => $user->department->department_code,
                    'permissions' => $user->role->permissions,
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * Request password reset link
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection error.',
            ], 503);
        }

        if (!$user || !$user->is_active) {
            return response()->json([
                'success' => true,
                'message' => 'If your email exists, a reset link has been sent.',
            ]);
        }

        try {
            $token = Password::broker()->createToken($user);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to generate reset token.',
            ], 500);
        }

        $appUrl = rtrim(config('app.url') ?: url('/'), '/');
        $resetUrl = $appUrl . '/password-reset/' . $token . '?email=' . urlencode($user->email);

        if (app()->environment('local')) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link generated.',
                'data' => [
                    'reset_url' => $resetUrl,
                ],
            ]);
        }

        try {
            Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $e) {
            // swallow to prevent leaking internal state
        }

        return response()->json([
            'success' => true,
            'message' => 'If your email exists, a reset link has been sent.',
        ]);
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    if (method_exists($user, 'tokens')) {
                        $user->tokens()->delete();
                    }

                    event(new PasswordReset($user));
                }
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection error.',
            ], 503);
        }

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset.',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'signature_url' => $user->signature_url,
                'employee_id' => $user->employee_id,
                'role' => [
                    'id' => $user->role->id,
                    'name' => $user->role->role_name,
                    'display_name' => $user->role->display_name,
                    'level' => $user->role->level,
                    'permissions' => $user->role->permissions,
                ],
                'department' => [
                    'id' => $user->department->id,
                    'name' => $user->department->department_name,
                    'code' => $user->department->department_code,
                ],
                'last_login_at' => $user->last_login_at,
                'unread_notifications' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        // Log activity
        ActivityLog::logActivity(
            'User',
            $user->id,
            'Logout',
            'User logged out',
            null,
            null,
            $user
        );

        // Revoke current token
        $token = $request->user()->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(Request $request)
    {
        $user = $request->user();

        // Revoke all tokens
        $user->tokens()->delete();

        // Log activity
        ActivityLog::logActivity(
            'User',
            $user->id,
            'Logout_All',
            'User logged out from all devices',
            null,
            null,
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices successfully',
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Log activity
        ActivityLog::logActivity(
            'User',
            $user->id,
            'Password_Changed',
            'User changed password',
            null,
            null,
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        $oldValues = $user->only(['name', 'phone']);
        
        $user->update($request->only(['name', 'phone']));

        // Log activity
        ActivityLog::logActivity(
            'User',
            $user->id,
            'Profile_Updated',
            'User updated profile',
            $oldValues,
            $user->only(['name', 'phone']),
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user,
        ]);
    }

    public function uploadSignature(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'signature' => 'sometimes|file|image|max:2048',
            'signature_data' => 'sometimes|string',
        ]);

        $path = null;

        if ($request->hasFile('signature')) {
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }

            $file = $request->file('signature');
            $filename = 'signature_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('signatures/user_' . $user->id, $filename, 'public');
        } elseif ($request->filled('signature_data')) {
            $data = $request->input('signature_data');

            if (!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $data, $matches)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature data format',
                ], 422);
            }

            $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
            $base64 = substr($data, strpos($data, ',') + 1);
            $decoded = base64_decode($base64, true);

            if ($decoded === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid base64 signature data',
                ], 422);
            }

            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }

            $filename = 'signature_' . $user->id . '_' . time() . '.' . $ext;
            $path = 'signatures/user_' . $user->id . '/' . $filename;
            Storage::disk('public')->put($path, $decoded);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No signature provided',
            ], 422);
        }

        $oldValues = $user->only(['signature_path']);
        $user->update(['signature_path' => $path]);

        ActivityLog::logActivity(
            'User',
            $user->id,
            'Signature_Updated',
            'User updated signature',
            $oldValues,
            $user->only(['signature_path']),
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Signature updated successfully',
            'data' => [
                'signature_url' => $user->fresh()->signature_url,
            ],
        ]);
    }

    public function deleteSignature(Request $request)
    {
        $user = $request->user();

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $oldValues = $user->only(['signature_path']);
        $user->update(['signature_path' => null]);

        ActivityLog::logActivity(
            'User',
            $user->id,
            'Signature_Deleted',
            'User deleted signature',
            $oldValues,
            $user->only(['signature_path']),
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Signature deleted successfully',
        ]);
    }
}
