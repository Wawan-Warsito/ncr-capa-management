<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NCRController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\DefectModeController;
use App\Http\Controllers\Api\NCRAttachmentController;
use App\Http\Controllers\Api\CAPAController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\PublicLinkController;
use App\Http\Middleware\CheckRole;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/signature', [AuthController::class, 'uploadSignature']);
        Route::delete('/signature', [AuthController::class, 'deleteSignature']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/company', [DashboardController::class, 'companyDashboard']);
        Route::get('/department', [DashboardController::class, 'departmentDashboard']);
        Route::get('/personal', [DashboardController::class, 'personalDashboard']);
        Route::get('/quick-stats', [DashboardController::class, 'quickStats']);
    });

    // NCR
    Route::prefix('ncrs')->group(function () {
        Route::get('/', [NCRController::class, 'index']);
        Route::post('/', [NCRController::class, 'store']);
        Route::get('/{id}', [NCRController::class, 'show']);
        Route::put('/{id}', [NCRController::class, 'update']);
        Route::delete('/{id}', [NCRController::class, 'destroy']);
        Route::get('/{id}/public-link', [PublicLinkController::class, 'getPublicLink']);
        
        // Workflow
        Route::post('/{id}/submit', [NCRController::class, 'submit']);
        Route::post('/{id}/approve', [NCRController::class, 'approve']);
        Route::post('/{id}/reject', [NCRController::class, 'reject']);
        
        // Attachments
        Route::post('/{id}/attachments', [NCRAttachmentController::class, 'store']);
        Route::delete('/attachments/{attachmentId}', [NCRAttachmentController::class, 'destroy']);
        Route::get('/attachments/{attachmentId}/download', [NCRAttachmentController::class, 'download']);
        
        // Import
        Route::get('/import-template', [ImportController::class, 'downloadNcrImportTemplate']);
        Route::post('/import', [ImportController::class, 'importNCR']);
    });

    // CAPA
    Route::prefix('capas')->group(function () {
        Route::get('/', [CAPAController::class, 'index']);
        Route::post('/', [CAPAController::class, 'store']);
        Route::get('/{id}', [CAPAController::class, 'show']);
        Route::put('/{id}', [CAPAController::class, 'update']);
        
        // Workflow
        Route::put('/{id}/progress', [CAPAController::class, 'updateProgress']);
        Route::post('/{id}/verify', [CAPAController::class, 'verify']);
        Route::post('/{id}/close', [CAPAController::class, 'close']);
    });

    // Reports & Exports
    Route::prefix('reports')->group(function () {
        Route::get('/summary', [ReportController::class, 'summary']);
        Route::get('/ncr', [ReportController::class, 'ncrReport']);
        Route::get('/capa', [ReportController::class, 'capaReport']);
        Route::get('/department-performance', [ReportController::class, 'departmentPerformance']);
        Route::get('/pareto', [ReportController::class, 'pareto']);
        Route::get('/export/ncr', [ExportController::class, 'exportNCR']); // Assuming ExportController has this
        Route::get('/export/capa', [ExportController::class, 'exportCAPA']);
    });

    // Admin Routes (Protected by Role)
    Route::prefix('admin')->middleware([CheckRole::class . ':Administrator,Super Admin'])->group(function () {
        // Users
        Route::get('/users/export', [ExportController::class, 'exportUsers']); // define BEFORE resource to avoid collision with {user}
        Route::post('/users/import', [ImportController::class, 'importUsers']);
        Route::apiResource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
        
        // NCR Admin Ops
        Route::delete('/ncrs', [NCRController::class, 'purge']);
        
        // Departments
        Route::apiResource('departments', DepartmentController::class);
        
        // Settings
        Route::get('/settings', [SettingController::class, 'index']);
        Route::put('/settings', [SettingController::class, 'updateBatch']);
        Route::put('/settings/{key}', [SettingController::class, 'update']);
    });

    // Master Data
    Route::get('/departments', function () {
        return response()->json(['success' => true, 'data' => \App\Models\Department::active()->get()]);
    });
    Route::get('/defect-categories', function () {
        return response()->json(['success' => true, 'data' => \App\Models\DefectCategory::active()->get()]);
    });
    Route::get('/severity-levels', function () {
        return response()->json(['success' => true, 'data' => \App\Models\SeverityLevel::active()->orderBy('priority')->get()]);
    });
    Route::get('/disposition-methods', function () {
        return response()->json(['success' => true, 'data' => \App\Models\DispositionMethod::active()->get()]);
    });
    Route::get('/defect-modes', [DefectModeController::class, 'index']); // New endpoint

    Route::prefix('master')->group(function () {
        Route::get('/departments', function () {
            return response()->json(['success' => true, 'data' => \App\Models\Department::active()->get()]);
        });
        Route::get('/roles', function () {
            return response()->json(['success' => true, 'data' => \App\Models\Role::active()->get()]);
        });
        Route::get('/defect-categories', function () {
            return response()->json(['success' => true, 'data' => \App\Models\DefectCategory::active()->get()]);
        });
        Route::get('/severity-levels', function () {
            return response()->json(['success' => true, 'data' => \App\Models\SeverityLevel::active()->orderBy('priority')->get()]);
        });
        Route::get('/disposition-methods', function () {
            return response()->json(['success' => true, 'data' => \App\Models\DispositionMethod::active()->get()]);
        });
        Route::get('/defect-modes', [DefectModeController::class, 'index']); // New endpoint
        Route::get('/users', function (Request $request) {
            $query = \App\Models\User::with(['role', 'department'])->active();
            if ($request->has('department_id')) $query->where('department_id', $request->department_id);
            if ($request->has('role_id')) $query->where('role_id', $request->role_id);
            return response()->json(['success' => true, 'data' => $query->get()]);
        });
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', function (Request $request) {
            return response()->json(['success' => true, 'data' => \App\Models\Notification::where('recipient_user_id', $request->user()->id)->orderBy('created_at', 'desc')->paginate(15)]);
        });
        Route::get('/unread', function (Request $request) {
            return response()->json(['success' => true, 'data' => \App\Models\Notification::where('recipient_user_id', $request->user()->id)->unread()->orderBy('created_at', 'desc')->get()]);
        });
        Route::post('/{id}/read', function ($id) {
            \App\Models\Notification::findOrFail($id)->markAsRead();
            return response()->json(['success' => true, 'message' => 'Notification marked as read']);
        });
        Route::post('/mark-all-read', function (Request $request) {
            \App\Models\Notification::where('recipient_user_id', $request->user()->id)->unread()->update(['is_read' => true, 'read_at' => now()]);
            return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
        });
    });

    // Public Settings (Authenticated)
    Route::prefix('settings')->group(function () {
        Route::get('/', function () {
            return response()->json(['success' => true, 'data' => \App\Models\Setting::public()->get()]);
        });
        Route::get('/{key}', function ($key) {
            return response()->json(['success' => true, 'data' => \App\Models\Setting::get($key)]);
        });
    });
});
