<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Services\FavoriteService;
use App\Services\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function loginForm(Seo $seo)
    {
        $seo->set(__('ui.account.login'))->routeAlternates('account.login');
        $seo->noindex = true;

        return view('site.account.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('account.index'));
    }

    public function registerForm(Seo $seo)
    {
        $seo->set(__('ui.account.register'))->routeAlternates('account.register');
        $seo->noindex = true;

        return view('site.account.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:191', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'website' => ['prohibited'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => mb_strtolower($data['email']),
            'password' => $data['password'],
            'preferred_locale' => app()->getLocale(),
        ]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.index');
    }

    public function index(Request $request, Seo $seo, FavoriteService $favorites)
    {
        $seo->set(__('ui.account.title'))->routeAlternates('account.index');
        $seo->noindex = true;

        return view('site.account.index', [
            'orders' => $request->user()->orders()->withCount('items')->latest()->limit(20)->get(),
            'favorites' => Product::published()->translatedIn()->forCard()->whereIn('id', $favorites->ids())->get(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
