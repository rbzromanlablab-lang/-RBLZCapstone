<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ProductionAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_active_admin_from_deployment_configuration(): void
    {
        config()->set('deployment.admin', [
            'name' => 'PARDS Administrator',
            'email' => 'admin@example.com',
            'password' => 'secure-test-password',
        ]);

        $this->seed(ProductionAdminSeeder::class);

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame('PARDS Administrator', $admin->name);
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('secure-test-password', $admin->password));
        $this->assertNotNull($admin->email_verified_at);
        $this->assertNotNull($admin->adminProfile);
    }
}
