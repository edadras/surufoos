<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function __construct(private TwoFactorService $twoFactor, private ActivityLogger $logger) {}

    public function edit(Request $request)
    {
        $user = $request->user();
        $pending = $request->session()->get('2fa_pending_secret');

        return view('admin.profile.edit', [
            'user' => $user,
            'pendingSecret' => $pending,
            'qr' => $pending ? $this->twoFactor->qrSvg($this->twoFactor->otpauthUrl($pending, $user->email)) : null,
            'recoveryCodes' => session('recovery_codes'),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);
        $user->fill(['name' => $data['name'], 'email' => mb_strtolower($data['email'])]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('success', 'پروفایل به‌روزرسانی شد.');
    }

    public function enableTwoFactor(Request $request)
    {
        $request->session()->put('2fa_pending_secret', $this->twoFactor->generateSecret());

        return redirect()->route('admin.profile');
    }

    public function confirmTwoFactor(Request $request)
    {
        $secret = $request->session()->get('2fa_pending_secret');
        $data = $request->validate(['code' => ['required', 'string']]);
        if (! $secret || ! $this->twoFactor->verify($secret, $data['code'])) {
            throw ValidationException::withMessages(['code' => 'کد تأیید درست نیست. ساعت گوشی را بررسی کنید.']);
        }

        $codes = $this->twoFactor->recoveryCodes();
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => now(),
        ])->save();
        $request->session()->forget('2fa_pending_secret');
        $request->session()->put('admin_2fa_passed', true);
        $this->logger->log('2fa_enabled', $user);

        return redirect()->route('admin.profile')->with('success', 'ورود دومرحله‌ای فعال شد.')->with('recovery_codes', $codes);
    }

    public function disableTwoFactor(Request $request)
    {
        $request->validate(['current_password' => ['required', 'current_password']]);
        $user = $request->user();
        $user->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
        $this->logger->log('2fa_disabled', $user);

        return back()->with('success', 'ورود دومرحله‌ای غیرفعال شد.');
    }
}
