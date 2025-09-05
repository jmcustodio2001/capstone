<?php

// Create admin user via Tinker
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'email_verified_at' => now(),
    'password' => Hash::make('admin123'),
    'remember_token' => Str::random(10),
    'role' => 'admin',
]);

echo "Admin user created successfully.\n";
