<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RegistrationOtpSender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    private const PENDING_REGISTRATION_SESSION_KEY = 'auth.pending_registration';

    public function __construct(private readonly RegistrationOtpSender $otpSender)
    {
    }

    public function create(): View
    {
        return view('auth.register', [
            'roles' => [
                User::ROLE_STAFF,
                User::ROLE_TEACHER,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->registrationValidator($request->all())->validate();
        $pendingRegistration = $this->buildPendingRegistrationPayload($validated);

        try {
            $this->sendOtp($request, $pendingRegistration);
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'email' => 'We could not send the OTP right now. Please try again shortly.',
            ]);
        }

        return redirect()
            ->route('register.verify')
            ->with('status', 'We sent a 6-digit OTP to '.$pendingRegistration['email'].'. Enter it below to finish creating your account.');
    }

    public function showVerificationForm(Request $request): View|RedirectResponse
    {
        $pendingRegistration = $this->pendingRegistration($request);

        if ($pendingRegistration === null) {
            return redirect()
                ->route('register')
                ->withErrors([
                    'email' => 'Enter your registration details first before verifying the OTP.',
                ]);
        }

        return view('auth.verify-registration-otp', [
            'email' => $pendingRegistration['email'],
            'maskedEmail' => $this->maskEmail($pendingRegistration['email']),
            'expiresInMinutes' => $this->otpExpireMinutes(),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $pendingRegistration = $this->pendingRegistration($request);

        if ($pendingRegistration === null) {
            return redirect()
                ->route('register')
                ->withErrors([
                    'email' => 'Your registration OTP session has expired. Please register again.',
                ]);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if (now()->greaterThan($pendingRegistration['otp_expires_at'])) {
            throw ValidationException::withMessages([
                'otp' => 'The OTP has expired. Click resend to get a new code.',
            ]);
        }

        if (! Hash::check($validated['otp'], $pendingRegistration['otp_hash'])) {
            throw ValidationException::withMessages([
                'otp' => 'The OTP you entered is incorrect.',
            ]);
        }

        if (User::query()->where('email', $pendingRegistration['email'])->exists()) {
            $request->session()->forget(self::PENDING_REGISTRATION_SESSION_KEY);

            return redirect()
                ->route('register')
                ->withErrors([
                    'email' => 'This Gmail address is already registered.',
                ])
                ->withInput([
                    'name' => $pendingRegistration['name'],
                    'email' => $pendingRegistration['email'],
                    'role' => $pendingRegistration['role'],
                ]);
        }

        $user = new User([
            'name' => $pendingRegistration['name'],
            'email' => $pendingRegistration['email'],
            'role' => $pendingRegistration['role'],
            'password' => $pendingRegistration['password'],
            'is_active' => true,
        ]);
        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();
        $user->syncRoleProfile([
            'role' => $pendingRegistration['role'],
        ]);

        $request->session()->forget(self::PENDING_REGISTRATION_SESSION_KEY);

        return redirect()
            ->route('login')
            ->with('status', 'Account created successfully. Your email has been verified through OTP.');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $pendingRegistration = $this->pendingRegistration($request);

        if ($pendingRegistration === null) {
            return redirect()
                ->route('register')
                ->withErrors([
                    'email' => 'Your registration session is no longer available. Please register again.',
                ]);
        }

        try {
            $this->sendOtp($request, $pendingRegistration);
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'otp' => 'We could not resend the OTP right now. Please try again shortly.',
            ]);
        }

        return redirect()
            ->route('register.verify')
            ->with('status', 'A new OTP has been sent to '.$pendingRegistration['email'].'.');
    }

    private function registrationValidator(array $input)
    {
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in([User::ROLE_STAFF, User::ROLE_TEACHER])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validator->after(function ($validator) use ($input): void {
            $email = strtolower((string) ($input['email'] ?? ''));

            if (! str_ends_with($email, '@gmail.com')) {
                $validator->errors()->add('email', 'Registration requires a Gmail address.');
            }
        });

        return $validator;
    }

    private function buildPendingRegistrationPayload(array $validated): array
    {
        return [
            'name' => trim($validated['name']),
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ];
    }

    private function sendOtp(Request $request, array $pendingRegistration): void
    {
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes($this->otpExpireMinutes());

        $this->otpSender->send(
            email: $pendingRegistration['email'],
            name: $pendingRegistration['name'],
            otpCode: $otpCode,
            expiresInMinutes: $this->otpExpireMinutes()
        );

        $request->session()->put(self::PENDING_REGISTRATION_SESSION_KEY, [
            'name' => $pendingRegistration['name'],
            'email' => $pendingRegistration['email'],
            'role' => $pendingRegistration['role'],
            'password' => $pendingRegistration['password'],
            'otp_hash' => Hash::make($otpCode),
            'otp_expires_at' => $expiresAt,
        ]);
    }

    private function pendingRegistration(Request $request): ?array
    {
        $pendingRegistration = $request->session()->get(self::PENDING_REGISTRATION_SESSION_KEY);

        if (! is_array($pendingRegistration)) {
            return null;
        }

        if (isset($pendingRegistration['otp_expires_at']) && ! $pendingRegistration['otp_expires_at'] instanceof Carbon) {
            $pendingRegistration['otp_expires_at'] = Carbon::parse((string) $pendingRegistration['otp_expires_at']);
        }

        return $pendingRegistration;
    }

    private function otpExpireMinutes(): int
    {
        return max(1, (int) config('services.registration_otp.expire', 10));
    }

    private function maskEmail(string $email): string
    {
        [$username, $domain] = explode('@', $email, 2);

        return Str::mask($username, '*', 2). '@'.$domain;
    }
}
