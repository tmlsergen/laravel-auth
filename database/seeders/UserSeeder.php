<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@auth.com',
            'role' => Role::ADMIN->value,
            'password' => Hash::make('Test1234!'),
        ]);

        $user = User::query()->create([
            'name' => 'User',
            'email' => 'user@auth.com',
            'role' => Role::USER->value,
            'password' => Hash::make('Test1234!'),
        ]);

        PasswordHistory::query()->create([
            'user_id' => $admin->id,
            'password' => $admin->password,
        ]);

        PasswordHistory::query()->create([
            'user_id' => $user->id,
            'password' => $user->password,
        ]);
    }
}
