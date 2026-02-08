<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        if (User::where('email', 'admin@kiosk.local')->exists()) {
            $this->command->info('Admin user already exists, skipping.');

            return;
        }

        User::create([
            'name' => 'Admin',
            'email' => 'admin@kiosk.local',
            'password' => Hash::make('admin123'),
        ]);

        $this->command->info('Admin user created:');
        $this->command->info('  Email: admin@kiosk.local');
        $this->command->info('  Password: admin123');
        $this->command->warn('Please change the password after first login!');
    }
}
