<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_log_in_with_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.account@gmail.com',
            'password' => 'secret123',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'secret123',
            'privacy_consent' => '1',
        ]);

        $response->assertRedirect('/profile');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_staff_can_log_in_with_valid_credentials(): void
    {
        $staff = User::factory()->create([
            'email' => 'staff.account@gmail.com',
            'password' => 'secret123',
            'role' => User::ROLE_STAFF,
        ]);

        $response = $this->post('/login', [
            'email' => $staff->email,
            'password' => 'secret123',
            'privacy_consent' => '1',
        ]);

        $response->assertRedirect('/profile');
        $this->assertAuthenticatedAs($staff);
    }

    public function test_teacher_can_log_in_with_valid_credentials(): void
    {
        $teacher = User::factory()->create([
            'email' => 'teacher.account@gmail.com',
            'password' => 'secret123',
            'role' => User::ROLE_TEACHER,
        ]);

        $response = $this->post('/login', [
            'email' => $teacher->email,
            'password' => 'secret123',
            'privacy_consent' => '1',
        ]);

        $response->assertRedirect('/profile');
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_login_allows_non_gmail_accounts_with_valid_credentials(): void
    {
        $staff = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => 'secret123',
            'role' => User::ROLE_STAFF,
        ]);

        $response = $this->post('/login', [
            'email' => $staff->email,
            'password' => 'secret123',
            'privacy_consent' => '1',
        ]);

        $response->assertRedirect('/profile');
        $this->assertAuthenticatedAs($staff);
    }

    public function test_login_requires_privacy_consent(): void
    {
        $staff = User::factory()->create([
            'email' => 'privacy.staff@gmail.com',
            'password' => 'secret123',
            'role' => User::ROLE_STAFF,
        ]);

        $response = $this->post('/login', [
            'email' => $staff->email,
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('privacy_consent');
        $this->assertGuest();
    }

    public function test_account_creation_rejects_non_gmail_email(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'owner@gmail.com',
        ]);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Second Admin',
            'email' => 'second-admin@yahoo.com',
            'role' => User::ROLE_ADMIN,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('email');
    }
}
