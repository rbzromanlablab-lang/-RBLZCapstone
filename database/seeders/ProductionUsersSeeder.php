<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class ProductionUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = json_decode((string) config('deployment.users_json'), true);
        $password = (string) config('deployment.user_password');

        if (! is_array($users) || $users === [] || $password === '') {
            throw new RuntimeException('PRODUCTION_USERS_JSON and PRODUCTION_USERS_PASSWORD must be configured.');
        }

        foreach ($users as $attributes) {
            $validated = Validator::make($attributes, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'role' => ['required', Rule::in(User::roles())],
            ])->validate();

            $user = User::query()->updateOrCreate(
                ['email' => strtolower($validated['email'])],
                [
                    'name' => $validated['name'],
                    'password' => Hash::make($password),
                    'role' => $validated['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoleProfile();
        }
    }
}
