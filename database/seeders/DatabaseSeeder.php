<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Production-safe: creates the first administrator when ADMIN_EMAIL and
     * ADMIN_PASSWORD are set in .env. No sample stock or leads.
     */
    public function run(): void
    {
        $email = config('hadeed.admin.email');
        $password = config('hadeed.admin.password');

        if ($email && $password) {
            User::firstOrCreate(['email' => strtolower($email)], [
                'name' => config('hadeed.admin.name'),
                'password' => Hash::make($password),
                'role' => Role::Admin,
                'active' => true,
            ]);
        }
    }
}
