<?php

namespace Database\Seeders;

use App\Models\Camera;
use Illuminate\Database\Seeder;

class CameraSeeder extends Seeder
{
    /**
     * Create default local camera for the system
     */
    public function run(): void
    {
        // Create a default local camera that's automatically activated
        Camera::firstOrCreate(
            ['name' => 'Default Local Camera'],
            [
                'location' => 'Admin Device',
                'type' => 'classroom',
                'is_local_device' => true,
                'is_active' => true,
                'device_identifier' => null,
            ]
        );
    }
}