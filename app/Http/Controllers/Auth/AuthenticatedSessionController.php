<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'privacy_consent' => ['accepted'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Please contact the administrator.',
            ]);
        }

        if (! $user || ! $this->passwordMatches($user, $credentials['password'])) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (! $user->profilePhoto()->exists()) {
            return redirect()->route('profile.edit')->with('success', 'Logged in successfully.');
        }

        return redirect()->intended($this->redirectPathByRole($user))->with('success', 'Logged in successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Logged out successfully.');
    }

    protected function redirectPathByRole(User $user): string
    {
        return match ($user->role) {
            User::ROLE_ADMIN => route('admin.dashboard', absolute: false),
            User::ROLE_STAFF => route('staff.dashboard', absolute: false),
            User::ROLE_TEACHER => route('teacher.dashboard', absolute: false),
            default => Route::has('login') ? route('login', absolute: false) : '/',
        };
    }

    protected function passwordMatches(User $user, string $plainPassword): bool
    {
        try {
            return Hash::check($plainPassword, $user->password);
        } catch (RuntimeException) {
            if ($user->password !== $plainPassword) {
                return false;
            }

            // Migrate legacy plain-text passwords to the current configured hash algorithm.
            $user->password = $plainPassword;
            $user->save();

            return true;
        }
    }
}
