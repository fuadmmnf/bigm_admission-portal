<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthUsersSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'ulid' => (string) Str::ulid(),
                'password' => Hash::make('password'),
            ]
        );

        $admin->syncRoles(['admin']);

        $moderator = User::query()->firstOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Moderator User',
                'ulid' => (string) Str::ulid(),
                'password' => Hash::make('password'),
            ]
        );

        $moderator->syncRoles(['moderator']);
    }
}

