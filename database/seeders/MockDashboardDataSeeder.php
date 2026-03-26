<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MockDashboardDataSeeder extends Seeder
{
    public function run()
    {
        // Change this if your test company ID is different
        $companyId = 62;
        $userId = 1;

        $ranges = ['North Beat A', 'West Ridge', 'River Buffer', 'East Plateau', 'South Valley'];
        $beats = ['Beat Alpha', 'Beat Beta', 'Beat Gamma', 'Beat Delta', 'Beat Echo'];
        $species = ['Sal', 'Saja', 'Sagaon', 'Beeja', 'Haldu', 'Tendu', 'Mahua'];
        $animals = ['Sloth Bear', 'Leopard', 'Hyena', 'Wild Boar', 'Spotted Deer'];

        // 1. Define the Event Types and their JSON Generators
        $reportTypes = [
            // --- CRIMINAL ACTIVITIES (Category: crimes) ---
            [
                'category' => 'crimes',
                'type' => 'Illegal Felling',
                'generator' => fn() => json_encode([
        'species' => $species[array_rand($species)],
        'qty' => rand(1, 15),
        'girth' => rand(50, 200),
        'volume' => rand(10, 100) / 10,
        'reason' => ['Trade', 'Fuel', 'Agri Land', 'Others'][rand(0, 3)]
        ])
            ],
            [
                'category' => 'crimes',
                'type' => 'Timber Transport',
                'generator' => fn() => json_encode([
        'produce_name' => 'Timber Logs',
        'qty_initial' => rand(10, 50),
        'vehicle_type' => ['Truck', 'Tractor', 'Tempo'][rand(0, 2)],
        'vehicle_no' => 'MH-' . rand(10, 49) . '-' . Str::upper(Str::random(2)) . '-' . rand(1000, 9999),
        'route' => 'Route ' . rand(1, 55)
        ])
            ],
            [
                'category' => 'crimes',
                'type' => 'Timber Storage',
                'generator' => fn() => json_encode([
        'species' => $species[array_rand($species)],
        'qty_cmt' => rand(5, 50),
        'storage_type' => ['Godown', 'Open Space'][rand(0, 1)]
        ])
            ],
            [
                'category' => 'crimes',
                'type' => 'Poaching',
                'generator' => fn() => json_encode([
        'species' => $animals[array_rand($animals)],
        'cause_death' => ['Snare', 'Poison', 'Gunshot', 'Electrocution'][rand(0, 3)],
        'carcass_state' => ['Fresh', 'Decomposed', 'Bones'][rand(0, 2)],
        'gender' => ['Male', 'Female', 'Unknown'][rand(0, 2)],
        'age_class' => ['Adult', 'Sub-Adult', 'Juvenile'][rand(0, 2)]
        ])
            ],
            [
                'category' => 'crimes',
                'type' => 'Encroachment',
                'generator' => fn() => json_encode([
        'encroachment_type' => ['Agriculture', 'Construction'][rand(0, 1)],
        'area_hectare' => rand(10, 100) / 10,
        'machinery' => ['Yes', 'No'][rand(0, 1)],
        'occupants' => rand(1, 10)
        ])
            ],
            [
                'category' => 'crimes',
                'type' => 'Illegal Mining',
                'generator' => fn() => json_encode([
        'mineral_type' => ['Sand', 'Gravel', 'Stone', 'Coal'][rand(0, 3)],
        'volume_cum' => rand(100, 1000),
        'mining_method' => ['Manual', 'Mechanized'][rand(0, 1)]
        ])
            ],

            // --- EVENTS & MONITORING (Category: events) ---
            [
                'category' => 'events',
                'type' => 'Animal Sighting',
                'generator' => fn() => json_encode([
        'species' => $animals[array_rand($animals)],
        'sighting_type' => ['Direct', 'Indirect'][rand(0, 1)],
        'num_animals' => rand(1, 5),
        'gender' => ['Male', 'Female', 'Unknown'][rand(0, 2)],
        'evidence_type' => ['Photo', 'Pugmark', 'Scat', 'Scratch'][rand(0, 3)]
        ])
            ],
            [
                'category' => 'events',
                'type' => 'Water Status',
                'generator' => fn() => json_encode([
        'source_type' => ['Earthen Pond', 'Dam', 'Stream', 'Well'][rand(0, 3)],
        'is_dry' => ['Yes', 'No'][rand(0, 1)],
        'water_quality' => ['Clear', 'Turbid', 'Contaminated'][rand(0, 2)],
        'animal_sign' => ['Pugmarks', 'Scat', 'None'][rand(0, 2)]
        ])
            ],
            [
                'category' => 'events',
                'type' => 'Compensation',
                'generator' => fn() => json_encode([
        'comp_type' => ['Crop damage', 'Cattle death', 'Human injury', 'House damage'][rand(0, 3)],
        'victim_name' => 'Villager ' . Str::random(4),
        'village' => 'Village ' . rand(1, 20),
        'amount_claimed' => rand(5000, 50000)
        ])
            ],

            // --- FIRE (Category: fire) ---
            [
                'category' => 'fire',
                'type' => 'fire',
                'generator' => fn() => json_encode([
        'fire_cause' => ['Natural', 'Negligent', 'Intentional', 'Unknown'][rand(0, 3)],
        'area_burnt' => rand(5, 50)
        ])
            ]
        ];

        // 2. Generate 20 records for each report type
        $reportsToInsert = [];

        foreach ($reportTypes as $rt) {
            for ($i = 0; $i < 20; $i++) {
                // Random Date between March 1, 2026 and March 30, 2026
                $date = Carbon::create(2026, 3, rand(1, 30), rand(8, 18), rand(0, 59), rand(0, 59));

                // Random Coordinate around Map Center (Lat: 21.640, Lng: 79.560)
                $lat = 21.640 + (mt_rand(-1000, 1000) / 10000);
                $lng = 79.560 + (mt_rand(-1000, 1000) / 10000);

                $reportsToInsert[] = [
                    'report_id' => 'RPT-' . strtoupper(Str::random(6)),
                    'user_id' => $userId,
                    'company_id' => $companyId,
                    'category' => $rt['category'],
                    'report_type' => $rt['type'],
                    'date_time' => $date->format('Y-m-d H:i:s'),
                    'date' => $date->format('Y-m-d'),
                    'time' => $date->format('H:i:s'),
                    'latitude' => (string)$lat,
                    'longitude' => (string)$lng,
                    'beat' => $beats[array_rand($beats)],
                    'range' => $ranges[array_rand($ranges)],
                    'report_data' => $rt['generator'](),
                    'status' => ['Pending', 'Verified', 'Resolved'][rand(0, 2)],
                    'created_at' => $date->format('Y-m-d H:i:s'),
                    'updated_at' => clone $date->addHours(rand(1, 24))->format('Y-m-d H:i:s'),
                ];
            }
        }

        // Insert Reports in Chunks to prevent memory overload
        foreach (array_chunk($reportsToInsert, 50) as $chunk) {
            DB::table('forest_reports')->insert($chunk);
        }

        $this->command->info("Seunded ~200 Forest Reports for March 2026.");

        // 3. Generate Assets (Assuming your table is named 'assets')
        $assetsToInsert = [];
        $assetCategories = ['Vehicles', 'Heavy Eq.', 'Checkposts', 'Drones'];

        for ($i = 0; $i < 20; $i++) {
            $date = Carbon::create(2026, 3, rand(1, 30), rand(8, 18), rand(0, 59), rand(0, 59));

            $assetsToInsert[] = [
                'company_id' => $companyId,
                'category' => $assetCategories[array_rand($assetCategories)],
                'condition' => ['Good', 'Maintenance', 'Repair'][rand(0, 2)],
                // If you have status or name columns, add them here:
                // 'name' => 'Asset ' . rand(100, 999),
                // 'status' => 'Active',
                'created_at' => $date->format('Y-m-d H:i:s'),
                'updated_at' => clone $date->addDays(rand(1, 5))->format('Y-m-d H:i:s'),
            ];
        }

        // Check if table exists before inserting to prevent crashes
        if (DB::getSchemaBuilder()->hasTable('assets')) {
            DB::table('assets')->insert($assetsToInsert);
            $this->command->info("Seeded 20 Assets for March 2026.");
        }
        else {
            $this->command->warn("Table 'assets' does not exist. Skipped asset seeding.");
        }
    }
}