<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $role_id
 * @property string|null $department_id
 * @property string|null $phone
 * @property string|null $employee_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_login_at
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $appends = [
        'signature_url',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'department_id',
        'phone',
        'signature_path',
        'employee_id',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the role of the user
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the department of the user
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get NCRs created by this user
     */
    public function createdNcrs(): HasMany
    {
        return $this->hasMany(NCR::class, 'created_by_user_id');
    }

    /**
     * Get NCRs assigned to this user as PIC
     */
    public function assignedNcrs(): HasMany
    {
        return $this->hasMany(NCR::class, 'assigned_pic_id');
    }

    /**
     * Check if user is Admin
     */
    public function isAdmin(): bool
    {
        if (!$this->role) return false;
        $key = strtolower((string) $this->role->role_name);
        $key = preg_replace('/\s+/', ' ', trim($key));
        $key = str_replace([' ', '-'], '_', $key);
        return in_array($key, ['admin', 'administrator', 'super_admin', 'superadmin'], true);
    }

    /**
     * Check if user is QC Manager
     */
    public function isQCManager(): bool
    {
        if (!$this->role) return false;
        $key = strtolower((string) $this->role->role_name);
        $key = preg_replace('/\s+/', ' ', trim($key));
        $key = str_replace([' ', '-'], '_', $key);
        return in_array($key, ['qc_manager', 'qcmanager'], true);
    }

    /**
     * Check if user is Department Manager
     */
    public function isDepartmentManager(): bool
    {
        if (!$this->role) return false;
        $key = strtolower((string) $this->role->role_name);
        $key = preg_replace('/\s+/', ' ', trim($key));
        $key = str_replace([' ', '-'], '_', $key);
        return in_array($key, ['department_manager', 'dept_manager', 'departmentmanager'], true);
    }

    /**
     * Check if user is NCR Coordinator
     */
    public function isNCRCoordinator(): bool
    {
        if (!$this->role) return false;
        $key = strtolower((string) $this->role->role_name);
        $key = preg_replace('/\s+/', ' ', trim($key));
        $key = str_replace([' ', '-'], '_', $key);
        return in_array($key, ['ncr_coordinator', 'ncrcoordinator'], true);
    }

    /**
     * Get CAPAs assigned to this user
     */
    public function assignedCapas(): HasMany
    {
        return $this->hasMany(CAPA::class, 'assigned_pic_id');
    }

    /**
     * Get notifications for this user
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipient_user_id');
    }

    /**
     * Get unread notifications
     */
    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    /**
     * Get comments by this user
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'commented_by_user_id');
    }

    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope to get users by role
     */
    public function scopeByRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->role_name === $roleName;
    }



    /**
     * Get user's full display name with department
     */
    public function getFullDisplayNameAttribute(): string
    {
        $dept = $this->department ? $this->department->department_code : 'N/A';
        return "{$this->name} ({$dept})";
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    public function getSignatureUrlAttribute(): ?string
    {
        if (!$this->signature_path) {
            return null;
        }

        return asset('storage/' . ltrim($this->signature_path, '/'));
    }
}
