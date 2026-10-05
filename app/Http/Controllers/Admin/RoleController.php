<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index', ['roles' => Role::with('permissions')->withCount('users')->get()]);
    }

    public function create()
    {
        return view('admin.roles.form', ['role' => new Role, 'permissions' => Permission::orderBy('group')->get()->groupBy('group')]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $role = Role::create(collect($data)->only(['key', 'name'])->all());
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'نقش ساخته شد.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.form', ['role' => $role->load('permissions'), 'permissions' => Permission::orderBy('group')->get()->groupBy('group')]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validated($request, $role);
        $role->update(collect($data)->only(['key', 'name'])->all());
        if ($role->key !== 'super_admin') {
            $role->permissions()->sync($data['permissions'] ?? []);
        }

        return back()->with('success', 'نقش به‌روزرسانی شد.');
    }

    public function destroy(Role $role)
    {
        abort_if($role->key === 'super_admin', 422, 'نقش مدیر کل قابل حذف نیست.');
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'حذف شد.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:64', 'regex:/^[a-z_]+$/', Rule::unique('roles')->ignore($role?->id)],
            'name' => ['required', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
    }
}
