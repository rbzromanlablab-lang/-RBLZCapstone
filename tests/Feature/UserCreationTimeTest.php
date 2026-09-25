<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserCreationTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation_time_uses_the_philippine_timezone_configuration(): void
    {
        $this->assertSame('Asia/Manila', config('app.timezone'));
        $this->assertSame('+08:00', config('database.connections.mysql.timezone'));

        $user = User::factory()->create([
            'created_at' => Carbon::create(2026, 9, 25, 12, 47, 0, 'Asia/Manila'),
        ]);

        $this->assertSame('Sep 25, 2026 12:47 PM', $user->created_at_for_display->format('M d, Y h:i A'));
    }
}
