<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Check if admin user already exists
        $existingUser = User::where('email', 'admin@example.com')->first();

        if ($existingUser) {
            $this->command->info('Admin user already exists with email: admin@example.com');
            $this->command->info('User ID: ' . $existingUser->id);
            $this->command->info('Name: ' . $existingUser->name);
            $this->command->info('Role: ' . ($existingUser->role ?? 'Not set'));
        }
    }
}
