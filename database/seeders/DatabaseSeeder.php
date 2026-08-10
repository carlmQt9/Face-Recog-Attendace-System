<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Default Admin Account ─────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@school.edu'],
            [
                'name'     => 'System Administrator',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        // ── Default System Settings ───────────────────────────────────────────
        $defaults = [
            ['key' => 'cooldown_seconds', 'value' => '5',             'description' => 'Seconds to ignore same student after a successful scan'],
            ['key' => 'speaker_volume',   'value' => '80',            'description' => 'Speaker volume level (0–100)'],
            ['key' => 'success_beep_url', 'value' => '/sounds/success.mp3', 'description' => 'Path to success beep audio file'],
            ['key' => 'error_beep_url',   'value' => '/sounds/error.mp3',   'description' => 'Path to error beep audio file'],
        ];

        foreach ($defaults as $setting) {
            SystemSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        // ── Default Camera Setup ──────────────────────────────────────────────
        $this->call(CameraSeeder::class);
    }
}
