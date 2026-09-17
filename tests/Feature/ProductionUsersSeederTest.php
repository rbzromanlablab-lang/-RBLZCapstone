<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ProductionUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionUsersSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_configured_users_with_role_profiles(): void
    {
        config()->set('deployment.users_json', json_encode([
            ['name' => 'Staff User', 'email' => 'staff@gmail.com', 'role' => User::ROLE_STAFF],
            ['name' => 'Teacher User', 'email' => 'teacher@gmail.com', 'role' => User::ROLE_TEACHER],
        ]));
        config()->set('deployment.user_password', 'secure-test-password');

        $this->seed(ProductionUsersSeeder::class);

        $staff = User::query()->where('email', 'staff@gmail.com')->firstOrFail();
        $teacher = User::query()->where('email', 'teacher@gmail.com')->firstOrFail();

        $this->assertSame(User::ROLE_STAFF, $staff->role);
        $this->assertTrue($staff->is_active);
        $this->assertTrue(Hash::check('secure-test-password', $staff->password));
        $this->assertNotNull($staff->staffProfile);

        $this->assertSame(User::ROLE_TEACHER, $teacher->role);
        $this->assertTrue($teacher->is_active);
        $this->assertTrue(Hash::check('secure-test-password', $teacher->password));
        $this->assertNotNull($teacher->teacherProfile);
    }
}
