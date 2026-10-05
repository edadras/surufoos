<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->query('type') === 'admins', fn ($q) => $q->where('is_admin', true))
            ->when($request->query('type') === 'customers', fn ($q) => $q->where('is_admin', false))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->query('q').'%')->orWhere('email', 'like', '%'.$request->query('q').'%')))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['is_active' => true]), 'roles' => Role::orderBy('id')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = User::create($data);
        $user->forceFill(['is_admin' => $request->boolean('is_admin')])->save();
        $user->roles()->sync($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('success', 'کاربر ساخته شد.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', ['user' => $user->load('roles'), 'roles' => Role::orderBy('id')->get()]);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        // An admin cannot lock themselves out.
        if ($user->is(auth()->user())) {
            $data['is_active'] = true;
            $request->merge(['is_admin' => true]);
        }
        $user->update($data);
        $user->forceFill(['is_admin' => $request->boolean('is_admin')])->save();
        if (! $user->is(auth()->user()) || $user->isSuperAdmin()) {
            $user->roles()->sync($data['roles'] ?? []);
        }
        if ($request->boolean('reset_2fa')) {
            $user->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
        }

        return back()->with('success', 'کاربر به‌روزرسانی شد.');
    }

    public function destroy(User $user)
    {
        abort_if($user->is(auth()->user()), 422, 'نمی‌توانید حساب خودتان را حذف کنید.');
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'کاربر حذف شد.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users')->ignore($user?->id)],
            'mobile' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', Password::min(10)->letters()->numbers()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);
        $data['email'] = mb_strtolower($data['email']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
