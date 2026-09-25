<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserCreationTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_utc_user_creation_time_is_displayed_in_philippine_time(): void
    {
        $this->assertSame('Asia/Manila', config('app.timezone'));
        $this->assertSame('+00:00', config('database.connections.mysql.timezone'));

        $user = User::factory()->create();
        DB::table('users')->where('id', $user->id)->update([
            'created_at' => '2026-09-25 04:47:00',
        ]);

        $user->refresh();
        $this->assertSame('Sep 25, 2026 12:47 PM', $user->created_at_for_display->format('M d, Y h:i A'));
    }

    public function test_new_user_creation_time_is_stored_in_utc_and_displayed_in_philippine_time(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 25, 12, 47, 0, 'Asia/Manila'));

        try {
            $user = User::factory()->create();

            $this->assertSame('2026-09-25 04:47:00', $user->getRawOriginal('created_at'));
            $this->assertSame('Sep 25, 2026 12:47 PM', $user->created_at_for_display->format('M d, Y h:i A'));
        } finally {
            Carbon::setTestNow();
        }
    }
}
