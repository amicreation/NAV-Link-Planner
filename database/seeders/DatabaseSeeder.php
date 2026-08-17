<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed System Administrator (amitsharma@navwireless.com / admin)
        $admin = User::firstOrCreate(
            ['email' => 'amitsharma@navwireless.com'],
            [
                'name' => 'Amit Sharma',
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'mobile_number' => '+91 9876543210',
                'company' => 'NAV Wireless Technologies',
                'designation' => 'Chief Administrator',
                'department' => 'Network Planning Division',
                'status' => 'active',
                'last_login_at' => now(),
            ]
        );

        // 2. Seed Full Technical Specialist
        $technical = User::firstOrCreate(
            ['email' => 'tech@navlinkplanner.com'],
            [
                'name' => 'Technical Specialist Alex',
                'password' => Hash::make('password123'),
                'role' => 'technical',
                'mobile_number' => '+91 9876543211',
                'company' => 'Optical & Wireless Systems',
                'designation' => 'Lead FSO/RF Link Engineer',
                'department' => 'Optical Communications R&D',
                'status' => 'active',
                'created_by_id' => $admin->id,
                'last_login_at' => now(),
            ]
        );

        // 3. Seed Field Engineer
        $engineer = User::firstOrCreate(
            ['email' => 'engineer@navlinkplanner.com'],
            [
                'name' => 'Field Engineer John',
                'password' => Hash::make('password123'),
                'role' => 'engineer',
                'mobile_number' => '+91 9123456789',
                'company' => 'Telecom Planning Corp',
                'designation' => 'Senior RF Planning Engineer',
                'department' => 'Wireless Engineering',
                'status' => 'active',
                'created_by_id' => $admin->id,
                'last_login_at' => now(),
            ]
        );

        // 3. Seed Sample Projects if none exist
        if (Project::count() === 0) {
            $proj1 = Project::create([
                'user_id' => $engineer->id,
                'name' => 'Backbone Link Site A to Site B',
                'customer_name' => 'Telecom Infra Corp',
                'engineer_name' => $engineer->name,
                'project_date' => now()->toDateString(),
                'technology' => 'rf',
            ]);

            Site::create([
                'project_id' => $proj1->id,
                'site_label' => 'A',
                'name' => 'Tower Alpha',
                'latitude' => 12.97159870,
                'longitude' => 77.59456270,
                'tower_height' => 30.0,
                'ground_elevation' => 920.0,
            ]);

            Site::create([
                'project_id' => $proj1->id,
                'site_label' => 'B',
                'name' => 'Tower Bravo',
                'latitude' => 13.03582450,
                'longitude' => 77.62514820,
                'tower_height' => 30.0,
                'ground_elevation' => 915.0,
            ]);

            $proj2 = Project::create([
                'user_id' => $admin->id,
                'name' => 'Metro Fiber Backup FSO Link',
                'customer_name' => 'Defense Network Division',
                'engineer_name' => $admin->name,
                'project_date' => now()->toDateString(),
                'technology' => 'fso',
            ]);

            Site::create([
                'project_id' => $proj2->id,
                'site_label' => 'A',
                'name' => 'HQ Building Node',
                'latitude' => 12.93524050,
                'longitude' => 77.62446210,
                'tower_height' => 25.0,
                'ground_elevation' => 910.0,
            ]);

            Site::create([
                'project_id' => $proj2->id,
                'site_label' => 'B',
                'name' => 'Data Center Node',
                'latitude' => 12.95123010,
                'longitude' => 77.64125020,
                'tower_height' => 28.0,
                'ground_elevation' => 905.0,
            ]);
        }

        // 4. Initial Audit Log
        AuditLog::create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'action' => 'SYSTEM_INITIALIZED',
            'description' => 'System initialized with Admin account (amitsharma@navwireless.com)',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'NAV LINK PLANNER Seeder',
            'created_at' => now(),
        ]);
    }
}
