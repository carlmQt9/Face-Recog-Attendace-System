<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FirstAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the first admin account with approval
        \App\Models\User::create([
            'name' => 'System Administrator',
            'email' => 'admin@dmcmes.edu.ph',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => null, // First admin has no approver
        ]);

        $this->command->info('First admin created successfully!');
        $this->command->info('Email: admin@dmcmes.edu.ph');
        $this->command->info('Password: admin123');
        $this->command->warn('Please change this password immediately after logging in!');
    }
}
