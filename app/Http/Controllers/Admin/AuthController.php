<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private ActivityLogger $logger) {}

    public function loginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        $user = User::where('email', mb_strtolower($data['email']))->first();
        if (! $user || ! $user->is_admin || ! $user->is_active || ! Auth::attempt(['email' => $user->email, 'password' => $data['password']], $request->boolean('remember'))) {
            $this->logger->log('login_failed', $user, 'ورود ناموفق: '.$data['email'], userId: $user?->id);
            throw ValidationException::withMessages(['email' => 'ایمیل یا رمز عبور اشتباه است.']);
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->saveQuietly();

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('admin_2fa_passed', false);

            return redirect()->route('admin.two-factor.challenge');
        }

        $this->logger->log('login', $user, 'ورود به پنل');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function challengeForm(Request $request)
    {
        if (! $request->user()->hasTwoFactorEnabled() || $request->session()->get('admin_2fa_passed')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.two-factor');
    }

    public function challenge(Request $request, TwoFactorService $twoFactor)
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:20']]);
        $user = $request->user();
        $code = trim($data['code']);

        $valid = $twoFactor->verify($user->two_factor_secret, $code);
        if (! $valid && in_array(mb_strtolower($code), $user->two_factor_recovery_codes ?? [], true)) {
            $user->two_factor_recovery_codes = array_values(array_diff($user->two_factor_recovery_codes, [mb_strtolower($code)]));
            $user->save();
            $valid = true;
        }
        if (! $valid) {
            throw ValidationException::withMessages(['code' => 'کد وارد شده معتبر نیست.']);
        }

        $request->session()->put('admin_2fa_passed', true);
        $this->logger->log('login', $user, 'ورود به پنل (دومرحله‌ای)');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $this->logger->log('logout', $request->user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
