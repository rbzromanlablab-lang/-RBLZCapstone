<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjaxFormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function ajaxHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    public function test_valid_ajax_login_returns_message_and_redirect(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'password' => 'password123',
        ]);

        $response = $this->withHeaders($this->ajaxHeaders())->post('/login', [
            'email' => $teacher->email,
            'password' => 'password123',
            'privacy_consent' => '1',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Saved successfully.')
            ->assertJsonPath('redirect', route('profile.edit'));
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_manual_redirect_errors_are_returned_as_ajax_validation_errors(): void
    {
        User::factory()->create([
            'email' => 'ajax.user@gmail.com',
            'password' => 'correct-password',
        ]);

        $response = $this->withHeaders($this->ajaxHeaders())->post('/login', [
            'email' => 'ajax.user@gmail.com',
            'password' => 'wrong-password',
            'privacy_consent' => '1',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('message', 'The provided credentials do not match our records.');
        $this->assertGuest();
    }

    public function test_ajax_validation_exception_returns_inline_field_errors(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->withHeaders($this->ajaxHeaders())
            ->post('/properties', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['property_name', 'property_code', 'quantity']);
    }

    public function test_ajax_save_returns_success_message_and_redirect_while_persisting_data(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->withHeaders($this->ajaxHeaders())
            ->post('/properties', [
                'property_name' => 'AJAX Laptop',
                'property_code' => 'AJAX-001',
                'quantity' => 1,
                'unit' => 'unit',
                'condition_status' => Property::CONDITION_GOOD,
                'status' => Property::STATUS_AVAILABLE,
            ]);

        $property = Property::where('property_code', 'AJAX-001')->firstOrFail();
        $response->assertOk()
            ->assertJsonPath('message', 'Property added successfully.')
            ->assertJsonPath('redirect', route('properties.show', $property));
        $this->assertStringStartsWith('SN-', $property->serial_number);
    }

    public function test_regular_non_ajax_forms_keep_redirect_behavior(): void
    {
        $teacher = User::factory()->create(['password' => 'password123']);

        $this->post('/login', [
            'email' => $teacher->email,
            'password' => 'password123',
            'privacy_consent' => '1',
        ])->assertRedirect('/profile');
    }

    public function test_ajax_handler_is_loaded_on_authenticated_and_authentication_pages(): void
    {
        $this->get('/login')->assertOk()->assertSee('data-ajax-notice', false);
        $this->get('/register')->assertOk()->assertSee('data-ajax-notice', false);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('data-ajax-notice', false);
    }
}
