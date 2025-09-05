<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
        } else {
            // Create new admin user
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'admin'
            ]);
            
            $this->command->info('Admin user created successfully!');
            $this->command->info('User ID: ' . $user->id);
            $this->command->info('Email: ' . $user->email);
            $this->command->info('Password: password123');
            $this->command->info('Role: ' . $user->role);
        }
    }
}
