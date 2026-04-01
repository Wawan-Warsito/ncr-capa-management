<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create essential roles and departments for testing
        $role = Role::create([
            'role_name' => 'Admin',
            'display_name' => 'Administrator',
            'description' => 'Admin Role',
            'permissions' => ['*'],
            'level' => 10,
        ]);

        $department = Department::create([
            'department_code' => 'IT',
            'department_name' => 'Information Technology',
            'is_active' => true,
        ]);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
            'role_id' => Role::first()->id,
            'department_id' => Department::first()->id,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'token',
                ]
            ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => Role::first()->id,
            'department_id' => Department::first()->id,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_deactivated_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'deactivated@example.com',
            'password' => Hash::make('password123'),
            'role_id' => Role::first()->id,
            'department_id' => Department::first()->id,
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'deactivated@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create([
            'role_id' => Role::first()->id,
            'department_id' => Department::first()->id,
        ]);

        $response = $this->actingAsApi($user)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => $user->email,
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'role_id' => Role::first()->id,
            'department_id' => Department::first()->id,
        ]);

        $response = $this->actingAsApi($user)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
    }
}
