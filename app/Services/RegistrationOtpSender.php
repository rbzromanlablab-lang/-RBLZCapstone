<?php

namespace App\Services;

use App\Mail\RegistrationOtpMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class RegistrationOtpSender
{
    public function send(string $email, string $name, string $otpCode, int $expiresInMinutes): void
    {
        $apiKey = (string) config('services.brevo.api_key');

        if ($apiKey === '') {
            Mail::to($email)->send(new RegistrationOtpMail(
                otpCode: $otpCode,
                name: $name,
                email: $email,
                expiresInMinutes: $expiresInMinutes
            ));

            return;
        }

        Http::acceptJson()
            ->withHeader('api-key', $apiKey)
            ->timeout(10)
            ->post((string) config('services.brevo.endpoint'), [
                'sender' => [
                    'email' => config('mail.from.address'),
                    'name' => config('mail.from.name'),
                ],
                'to' => [[
                    'email' => $email,
                    'name' => $name,
                ]],
                'subject' => 'Your PARDS Registration OTP Code',
                'htmlContent' => view('emails.registration-otp', [
                    'otpCode' => $otpCode,
                    'name' => $name,
                    'email' => $email,
                    'expiresInMinutes' => $expiresInMinutes,
                ])->render(),
            ])
            ->throw();
    }
}
