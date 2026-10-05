<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'suru:admin {email} {--name=مدیر سایت} {--password= : Leave empty to be prompted} {--role=super_admin}';

    protected $description = 'Create (or promote) an admin account for the Persian admin panel';

    public function handle(): int
    {
        $password = $this->option('password') ?: $this->secret('Password (min 10 chars, letters + numbers)');
        $validator = Validator::make(['email' => $this->argument('email'), 'password' => $password], [
            'email' => ['required', 'email'],
            'password' => ['required', Password::min(10)->letters()->numbers()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => mb_strtolower($this->argument('email'))]);
        $user->fill(['name' => $user->name ?: $this->option('name'), 'password' => $password, 'is_active' => true]);
        $user->is_admin = true;
        $user->save();

        $role = Role::where('key', $this->option('role'))->first();
        if (! $role) {
            $this->error('Role not found. Run `php artisan db:seed` first.');

            return self::FAILURE;
        }
        $user->roles()->syncWithoutDetaching([$role->id]);
        $this->info("Admin ready: {$user->email} ({$role->name}). Login at ".url('/admin/login'));

        return self::SUCCESS;
    }
}
