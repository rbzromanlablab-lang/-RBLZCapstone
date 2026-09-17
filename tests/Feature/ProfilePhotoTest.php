<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    private function png(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWZkAAAAASUVORK5CYII=');
    }

    public function test_user_can_upload_replace_and_view_only_their_own_photo(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertOk();
        $this->put('/profile', [
            'photo' => UploadedFile::fake()->createWithContent('portrait.png', $this->png()),
            'user_id' => $other->id,
        ])->assertRedirect('/dashboard');

        $this->assertTrue($user->profilePhoto()->exists());
        $this->assertFalse($other->profilePhoto()->exists());
        $this->get('/profile/photo')->assertOk()->assertHeader('Content-Type', 'image/png')
            ->assertContent($this->png());
        $this->get('/teacher/dashboard')->assertOk()->assertSee('alt="Your profile picture"', false);

        $this->put('/profile', [
            'photo' => UploadedFile::fake()->createWithContent('replacement.png', $this->png()),
        ])->assertRedirect('/dashboard');
        $this->assertDatabaseCount('profile_photos', 1);

        $this->actingAs($other)->get('/profile/photo')->assertNotFound();
    }

    public function test_photo_upload_rejects_missing_invalid_and_oversized_files(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/profile', [])->assertSessionHasErrors('photo');
        $this->put('/profile', [
            'photo' => UploadedFile::fake()->createWithContent('fake.png', '<script>alert(1)</script>'),
        ])->assertSessionHasErrors('photo');
        $this->put('/profile', [
            'photo' => UploadedFile::fake()->createWithContent('large.png', $this->png())->size(2049),
        ])->assertSessionHasErrors('photo');
        $this->assertDatabaseCount('profile_photos', 0);
    }

    public function test_guests_cannot_access_or_change_profile_photos(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/profile/photo')->assertRedirect('/login');
        $this->put('/profile', [])->assertRedirect('/login');
    }

    public function test_users_with_photos_go_to_their_dashboard_on_subsequent_logins(): void
    {
        foreach (['admin', 'staff', 'teacher'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $user->profilePhoto()->create([
                'mime_type' => 'image/png',
                'contents' => base64_encode($this->png()),
            ]);

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
                'privacy_consent' => '1',
            ])->assertRedirect('/'.$role.'/dashboard');
            $this->post('/logout');
        }
    }
}
