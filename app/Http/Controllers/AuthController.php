<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    private const REGISTRATION_LIMIT = 5;
    private const REGISTRATION_DURATION = 3600; // 1 hour
    private const LOGIN_ATTEMPTS_LIMIT = 5;
    private const LOCKOUT_DURATION = 900; // 15 minutes

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Check rate limiting on login attempts
        $key = 'login_attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->withErrors(['email' => 'Too many login attempts. Please try again later.']);
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Sanitize inputs
        $credentials['email'] = trim(strip_tags($credentials['email']));

        // Check if account is locked
        $loginAttempt = LoginAttempt::where('email', $credentials['email'])->first();
        if ($loginAttempt && $loginAttempt->isLocked()) {
            RateLimiter::hit($key);
            return back()
                ->withErrors(['email' => 'Too many login attempts. Account locked for ' . $loginAttempt->remainingLockoutTime() . ' minutes.'])
                ->onlyInput('email');
        }

        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();

        // Check if account is manually locked
        if ($user && $user->isAccountLocked()) {
            RateLimiter::hit($key);
            return back()
                ->withErrors(['email' => 'Your account is temporarily locked. Please contact support.'])
                ->onlyInput('email');
        }

        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($key);
            
            // Reset login attempts on successful login
            if ($loginAttempt) {
                $loginAttempt->reset();
            }

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // Track failed login attempt
        RateLimiter::hit($key);
        $this->trackFailedLoginAttempt($credentials['email']);

        return back()
            ->withErrors(['email' => 'Invalid credentials.'])
            ->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Check registration rate limiting
        $key = 'register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, self::REGISTRATION_LIMIT)) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Registration rate limited. Please try again later.']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', $this->passwordRules()],
            'role' => 'required|in:admin,faculty',
            'admin_code' => 'nullable|string',
        ], [
            'name.regex' => 'Full name must contain only letters and spaces.',
            'password.regex' => 'Password must contain at least one uppercase letter, one number, and one special character (!@#$%^&*).',
        ]);

        // Sanitize inputs
        $data['name'] = trim(strip_tags($data['name']));
        $data['email'] = trim(strip_tags($data['email']));

        if ($data['role'] === 'admin') {
            if (empty($data['admin_code']) || $data['admin_code'] !== config('app.admin_registration_code')) {
                RateLimiter::hit($key);
                return back()
                    ->withInput()
                    ->withErrors(['admin_code' => 'Invalid authorization code.']);
            }
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'email_verified_at' => now(),
        ]);

        RateLimiter::hit($key);

        // Log activity
        ActivityLog::create([
            'event' => 'user.registered',
            'description' => "User {$user->email} registered with role {$user->role}",
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'user_id' => $user->id,
        ]);

        return redirect()->route('login')
            ->with('success', 'Registration successful! Please log in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Get password validation rules
     */
    private function passwordRules()
    {
        return 'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_])(?=.{8,}).*$/';
    }

    /**
     * Track failed login attempt
     */
    private function trackFailedLoginAttempt(string $email): void
    {
        $loginAttempt = LoginAttempt::firstOrCreate(
            ['email' => $email],
            ['attempts' => 0, 'last_attempt_at' => null, 'locked_until' => null]
        );

        $loginAttempt->incrementAttempt();
    }
}
