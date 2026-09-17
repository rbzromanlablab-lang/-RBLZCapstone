<?php

namespace Tests\Feature;

use App\Mail\RegistrationOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_otp_before_creating_the_account(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'Alvin Baguinon',
            'email' => 'alvinbaguinon27@gmail.com',
            'role' => User::ROLE_TEACHER,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/register/verify');

        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail): bool {
            return $mail->hasTo('alvinbaguinon27@gmail.com')
                && preg_match('/^\d{6}$/', $mail->otpCode) === 1;
        });

        $this->assertDatabaseMissing('users', [
            'email' => 'alvinbaguinon27@gmail.com',
            'role' => User::ROLE_TEACHER,
        ]);
    }

    public function test_registration_can_send_otp_through_the_brevo_api(): void
    {
        Mail::fake();
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 'test-message-id'], 201),
        ]);
        config()->set('services.brevo.api_key', 'test-api-key');
        config()->set('mail.from.address', 'sender@example.com');
        config()->set('mail.from.name', 'PARDS');

        $response = $this->post('/register', [
            'name' => 'API User',
            'email' => 'api.user@gmail.com',
            'role' => User::ROLE_TEACHER,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/register/verify');
        Mail::assertNothingSent();
        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'test-api-key')
                && $request['sender']['email'] === 'sender@example.com'
                && $request['to'][0]['email'] === 'api.user@gmail.com'
                && $request['subject'] === 'Your PARDS Registration OTP Code';
        });
    }

    public function test_user_can_complete_registration_with_a_valid_otp(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name' => 'Alvin Baguinon',
            'email' => 'alvinbaguinon27@gmail.com',
            'role' => User::ROLE_TEACHER,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $otpCode = null;

        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail) use (&$otpCode): bool {
            if (! $mail->hasTo('alvinbaguinon27@gmail.com')) {
                return false;
            }

            $otpCode = $mail->otpCode;

            return true;
        });

        $this->assertNotNull($otpCode);

        $response = $this->post('/register/verify', [
            'otp' => $otpCode,
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', [
            'email' => 'alvinbaguinon27@gmail.com',
            'role' => User::ROLE_TEACHER,
        ]);

        $user = User::query()->where('email', 'alvinbaguinon27@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->teacherProfile);
    }

    public function test_registration_verification_rejects_an_invalid_otp(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name' => 'Alvin Baguinon',
            'email' => 'alvinbaguinon27@gmail.com',
            'role' => User::ROLE_STAFF,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $actualOtp = null;

        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail) use (&$actualOtp): bool {
            $actualOtp = $mail->otpCode;

            return true;
        });

        $invalidOtp = $actualOtp === '000000' ? '999999' : '000000';

        $response = $this->post('/register/verify', [
            'otp' => $invalidOtp,
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertDatabaseMissing('users', [
            'email' => 'alvinbaguinon27@gmail.com',
        ]);
    }

    public function test_registration_rejects_admin_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Bad Admin',
            'email' => 'badadmin@gmail.com',
            'role' => User::ROLE_ADMIN,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_registration_requires_gmail_address(): void
    {
        $response = $this->post('/register', [
            'name' => 'Local User',
            'email' => 'local@example.com',
            'role' => User::ROLE_STAFF,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
