<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $qcManager;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create([
            'role_name' => 'Administrator',
            'display_name' => 'Administrator',
            'permissions' => ['*'],
            'level' => 10,
        ]);

        $qcManagerRole = Role::create([
            'role_name' => 'QC_Manager',
            'display_name' => 'QC Manager',
            'permissions' => ['ncr.view', 'ncr.approve', 'capa.view', 'capa.manage'],
            'level' => 8,
        ]);

        $userRole = Role::create([
            'role_name' => 'User',
            'display_name' => 'User',
            'permissions' => ['ncr.view', 'capa.view'],
            'level' => 1,
        ]);

        // Create department
        $dept = Department::create([
            'department_code' => 'IT',
            'department_name' => 'Information Technology',
        ]);

        // Create users
        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'department_id' => $dept->id,
        ]);

        $this->qcManager = User::factory()->create([
            'role_id' => $qcManagerRole->id,
            'department_id' => $dept->id,
        ]);

        $this->user = User::factory()->create([
            'role_id' => $userRole->id,
            'department_id' => $dept->id,
        ]);
    }

    public function test_admin_can_access_user_management()
    {
        $response = $this->actingAsApi($this->admin)
            ->getJson('/api/admin/users');

        $response->assertStatus(200);
    }

    public function test_qc_manager_cannot_access_user_management()
    {
        $response = $this->actingAsApi($this->qcManager)
            ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_access_user_management()
    {
        $response = $this->actingAsApi($this->user)
            ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_department_management()
    {
        $response = $this->actingAsApi($this->admin)
            ->getJson('/api/admin/departments');

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_admin_routes()
    {
        $response = $this->actingAsApi($this->user)
            ->getJson('/api/admin/settings');

        $response->assertStatus(403);
    }
}
