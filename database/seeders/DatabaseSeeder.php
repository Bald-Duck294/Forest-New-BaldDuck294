<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $this->seedRoles();

        // 2. Create Companies (10)
        $companies = $this->seedCompanies();

        // 3. Create Client Details (100)
        $clients = $this->seedClientDetails($companies);

        // 4. Create Site Details (100)
        $sites = $this->seedSiteDetails($companies, $clients);

        // 5. Create Users (100+)
        $users = $this->seedUsers($companies);

        // 6. Create Site Assignments (100)
        $this->seedSiteAssignments($users, $companies, $clients, $sites);

        // 7. Create Site Geofences (100)
        $this->seedSiteGeofences($companies, $sites);

        // 8. Create Patrol Sessions (100)
        $patrolSessions = $this->seedPatrolSessions($users, $companies);

        // 9. Create Patrol Logs (100)
        $this->seedPatrolLogs($patrolSessions, $companies);

        // 10. Create Attendance (100)
        $this->seedAttendance($users, $companies, $sites);

        // 11. Create Incidents (100)
        $this->seedIncidents($companies, $sites, $users);
       

        echo "✅ Database seeded successfully with 100+ records per table!\n";
    }

    private function seedRoles()
    {
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'SuperAdmin', 'description' => 'Can see everything', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Supervisor', 'description' => 'Can see only guards under them', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Guard/Employee', 'description' => 'No login access', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'name' => 'Admin', 'description' => 'Can see clients, supervisors and guards', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedCompanies()
    {
        $companies = [];
        for ($i = 1; $i <= 10; $i++) {
            $companies[] = [
                'name' => "Company $i - " . fake()->company(),
                'email' => "company$i@example.com",
                'logo' => 'logos/company' . $i . '.png',
                'isActive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('companies')->insert($companies);
        return DB::table('companies')->get();
    }

    private function seedClientDetails($companies)
    {
        $clients = [];
        $companyIds = $companies->pluck('id')->toArray();

        for ($i = 0; $i < 100; $i++) {
            $clients[] = [
                'company_id' => $companyIds[array_rand($companyIds)],
                'name' => fake()->company(),
                'email' => fake()->email(),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'isActive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('client_details')->insert($clients);
        return DB::table('client_details')->get();
    }

    private function seedSiteDetails($companies, $clients)
    {
        $sites = [];
        $companyIds = $companies->pluck('id')->toArray();
        $clientIds = $clients->pluck('id')->toArray();
        $clientMap = [];
        foreach ($clients as $client) {
            $clientMap[$client->id] = $client->name;
        }

        for ($i = 0; $i < 100; $i++) {
            $clientId = $clientIds[array_rand($clientIds)];

            $sites[] = [
                'company_id' => $companyIds[array_rand($companyIds)],
                'client_id' => $clientId,
                'name' => 'Site/Beat ' . fake()->word() . ' ' . ($i + 1),
                'client_name' => $clientMap[$clientId],
                'location' => fake()->address(),
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
                'isActive' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('site_details')->insert($sites);
        return DB::table('site_details')->get();
    }

    private function seedUsers($companies)
    {
        $users = [];
        $companyIds = $companies->pluck('id')->toArray();
        $roleIds = [1, 2, 3, 7]; // SuperAdmin, Supervisor, Guard, Admin

        // Create 100 users
        for ($i = 0; $i < 100; $i++) {
            $roleId = $roleIds[array_rand($roleIds)];
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $contact = fake()->numerify('9#########'); // Indian phone format

            $users[] = [
                'company_id' => $companyIds[array_rand($companyIds)],
                'role_id' => $roleId,
                'name' => "$firstName $lastName",
                'email' => strtolower($firstName . '.' . $lastName . '@example.com'),
                'contact' => $contact,
                'phone' => $contact,
                'password' => bcrypt('password123'),
                'isActive' => true,
                'profile_pic' => 'avatars/user' . ($i + 1) . '.png',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('users')->insert($users);
        return DB::table('users')->get();
    }

    private function seedSiteAssignments($users, $companies, $clients, $sites)
    {
        $assignments = [];
        $userIds = $users->pluck('id')->toArray();
        $companyIds = $companies->pluck('id')->toArray();
        $clientIds = $clients->pluck('id')->toArray();
        $siteIds = $sites->pluck('id')->toArray();

        $clientMap = [];
        $siteMap = [];
        foreach ($clients as $client) {
            $clientMap[$client->id] = $client->name;
        }
        foreach ($sites as $site) {
            $siteMap[$site->id] = $site->name;
        }

        for ($i = 0; $i < 100; $i++) {
            $userId = $userIds[array_rand($userIds)];
            $clientId = $clientIds[array_rand($clientIds)];
            $siteId = $siteIds[array_rand($siteIds)];

            $assignments[] = [
                'company_id' => $companyIds[array_rand($companyIds)],
                'user_id' => $userId,
                'supervisor_id' => $userIds[array_rand($userIds)],
                'client_id' => $clientId,
                'site_id' => $siteId,
                'client_name' => $clientMap[$clientId],
                'site_name' => $siteMap[$siteId],
                'date_assigned' => fake()->date(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('site_assign')->insert($assignments);
    }

    private function seedSiteGeofences($companies, $sites)
    {
        $geofences = [];
        $companyIds = $companies->pluck('id')->toArray();
        $siteIds = $sites->pluck('id')->toArray();
        $types = ['circle', 'polygon'];

        for ($i = 0; $i < 100; $i++) {
            $type = $types[array_rand($types)];

            $geofences[] = [
                'company_id' => $companyIds[array_rand($companyIds)],
                'site_id' => $siteIds[array_rand($siteIds)],
                'name' => 'Geofence ' . ($i + 1),
                'type' => $type,
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
                'radius' => $type === 'circle' ? fake()->numberBetween(100, 1000) : null,
                'poly_lat_lng' => $type === 'polygon' ? json_encode([
                    [fake()->latitude(), fake()->longitude()],
                    [fake()->latitude(), fake()->longitude()],
                    [fake()->latitude(), fake()->longitude()],
                ]) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('site_geofences')->insert($geofences);
    }

    private function seedPatrolSessions($users, $companies)
    {
        $sessions = [];
        $userIds = $users->pluck('id')->toArray();
        $companyIds = $companies->pluck('id')->toArray();
        $sessionTypes = ['Foot', 'Vehicle', 'Bike'];
        $patrolTypes = ['Scheduled', 'Emergency', 'Routine'];

        for ($i = 0; $i < 100; $i++) {
            $startDate = fake()->dateTimeBetween('-30 days');
            $endDate = (clone $startDate)->modify('+' . rand(1, 8) . ' hours');

            $sessions[] = [
                'company_id' => $companyIds[array_rand($companyIds)],
                'user_id' => $userIds[array_rand($userIds)],
                'site_id' => rand(1, 50),
                'session' => $sessionTypes[array_rand($sessionTypes)],
                'type' => $patrolTypes[array_rand($patrolTypes)],
                'started_at' => $startDate,
                'ended_at' => $endDate,
                'start_lat' => fake()->latitude(),
                'start_lng' => fake()->longitude(),
                'end_lat' => fake()->latitude(),
                'end_lng' => fake()->longitude(),
                'distance' => fake()->randomFloat(2, 0.5, 50),
                'path_geojson' => json_encode([
                    'type' => 'LineString',
                    'coordinates' => [
                        [fake()->longitude(), fake()->latitude()],
                        [fake()->longitude(), fake()->latitude()],
                        [fake()->longitude(), fake()->latitude()],
                    ]
                ]),
                'created_at' => $startDate,
                'updated_at' => now(),
            ];
        }
        DB::table('patrol_sessions')->insert($sessions);
        return DB::table('patrol_sessions')->get();
    }

    private function seedPatrolLogs($patrolSessions, $companies)
    {
        $logs = [];
        $sessionIds = $patrolSessions->pluck('id')->toArray();
        $companyIds = $companies->pluck('id')->toArray();
        $eventTypes = ['animal_sighting', 'animal_mortality', 'trespassing', 'fire_alert', 'equipment_issue', 'poaching_attempt'];

        for ($i = 0; $i < 100; $i++) {
            $logs[] = [
                'company_id' => $companyIds[array_rand($companyIds)],
                'patrol_session_id' => $sessionIds[array_rand($sessionIds)],
                'type' => $eventTypes[array_rand($eventTypes)],
                'subtype' => fake()->word(),
                'description' => fake()->text(100),
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
                'images' => json_encode(['image' . ($i + 1) . '.jpg']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('patrol_logs')->insert($logs);
    }

    private function seedAttendance($users, $companies, $sites)
    {
        $attendance = [];
        $userIds = $users->pluck('id')->toArray();
        $companyIds = $companies->pluck('id')->toArray();
        $siteIds = $sites->pluck('id')->toArray();

        for ($i = 0; $i < 100; $i++) {
            $date = fake()->dateTimeBetween('-30 days');
            $checkIn = fake()->time('H:i:s');
            $checkOut = date('H:i:s', strtotime($checkIn . ' +8 hours'));

            $attendance[] = [
                'company_id' => $companyIds[array_rand($companyIds)],
                'user_id' => $userIds[array_rand($userIds)],
                'site_id' => $siteIds[array_rand($siteIds)],
                'dateFormat' => $date->format('Y-m-d'),
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'attendance_flag' => true,
                'lateTime' => fake()->numberBetween(0, 60),
                'start_lat' => fake()->latitude(),
                'start_lng' => fake()->longitude(),
                'created_at' => $date,
                'updated_at' => now(),
            ];
        }
        DB::table('attendance')->insert($attendance);
    }

    private function seedIncidents($companies, $sites, $users)
    {
        $incidents = [];
        $companyIds = $companies->pluck('id')->toArray();
        $siteIds = $sites->pluck('id')->toArray();
        $userIds = $users->pluck('id')->toArray();
        $incidentTypes = ['wildlife_sighting', 'trespassing', 'fire', 'injury', 'equipment_damage', 'poaching'];
        $statuses = [0, 1]; // 0: Pending, 1: Resolved

        for ($i = 0; $i < 100; $i++) {
            $date = fake()->dateTimeBetween('-30 days');

            $incidents[] = [
                'company_id' => $companyIds[array_rand($companyIds)],
                'site_id' => $siteIds[array_rand($siteIds)],
                'guard_id' => $userIds[array_rand($userIds)],
                'dateFormat' => $date->format('Y-m-d'),
                'type' => $incidentTypes[array_rand($incidentTypes)],
                'statusFlag' => $statuses[array_rand($statuses)],
                'description' => fake()->text(200),
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
                'images' => json_encode(['incident' . ($i + 1) . '.jpg']),
                'created_at' => $date,
                'updated_at' => now(),
            ];
        }
        DB::table('incidence_details')->insert($incidents);
    }
}
