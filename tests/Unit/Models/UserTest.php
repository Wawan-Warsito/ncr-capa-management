<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_belongs_to_role()
    {
        $role = Role::create([
            'role_name' => 'Admin',
            'description' => 'Administrator Role'
        ]);

        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals($role->id, $user->role->id);
    }

    public function test_user_belongs_to_department()
    {
        $department = Department::create([
            'department_code' => 'IT',
            'department_name' => 'Information Technology'
        ]);

        $user = User::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $user->department);
        $this->assertEquals($department->id, $user->department->id);
    }

    public function test_is_admin_check()
    {
        $adminRole = Role::create(['role_name' => 'Admin', 'description' => 'Admin']);
        $userRole = Role::create(['role_name' => 'User', 'description' => 'User']);

        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_qc_manager_check()
    {
        $qcManagerRole = Role::create(['role_name' => 'qc_manager', 'description' => 'QC Manager']);
        $userRole = Role::create(['role_name' => 'User', 'description' => 'User']);

        $qcManager = User::factory()->create(['role_id' => $qcManagerRole->id]);
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $this->assertTrue($qcManager->isQCManager());
        $this->assertFalse($user->isQCManager());
    }

    public function test_scope_active()
    {
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);
        User::factory()->create(['is_active' => true]);

        $this->assertEquals(2, User::active()->count());
    }
}
