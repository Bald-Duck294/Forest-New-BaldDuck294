<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlantationWorkflowSeeder extends Seeder
{
    public function run(): void
    {

        // GRIDS
        DB::table('grids')->insert([
            [
                'grid_code' => 'GRID-A1',
                'geo_polygon' => json_encode([]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'grid_code' => 'GRID-B1',
                'geo_polygon' => json_encode([]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);


        // PLANTATIONS
        DB::table('plantations')->insert([
            [
                'code' => 'PLT001',
                'name' => 'Green Valley Plantation',
                'description' => 'Demo plantation',
                'current_phase' => 'planning',
                'status' => 'active',
                'grid_id' => 1,
                'created_by' => 1,
                'approved_by' => 1,
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);


        // PLANTATION LOCATION
        DB::table('plantation_locations')->insert([
            [
                'plantation_id' => 1,
                'geo_polygon' => json_encode([]),
                'center_lat' => 19.0760,
                'center_lng' => 72.8777,
                'area_sq_m' => 1500,
                'verified_at' => now(),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);


        // PHASE LOG
        DB::table('plantation_phase_logs')->insert([
            [
                'plantation_id' => 1,
                'previous_phase' => 'identification',
                'new_phase' => 'measurement',
                'changed_by' => 1,
                'remarks' => 'Phase updated',
                'changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);


        // IDENTIFICATION
        DB::table('identification_details')->insert([
            [
                'plantation_id' => 1,
                'land_owner_name' => 'Govt Authority',
                'land_type' => 'govt',
                'ownership_document' => 'doc123.pdf',
                'site_photos' => json_encode(['photo1.jpg']),
                'is_verified' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);


        // MEASUREMENT
        DB::table('measurement_details')->insert([
            [
                'plantation_id' => 1,
                'total_area_sq_m' => 1500,
                'soil_type' => 'clay',
                'water_source_available' => true,
                'slope_type' => 'flat',
                'accessibility_notes' => 'Good road access',
                'feasibility_score' => 8.5,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);


        // PLANNING
        DB::table('planning_details')->insert([
            [
                'plantation_id' => 1,
                'plant_species' => 'Neem',
                'plant_count' => 200,
                'spacing_pattern' => '3x3',
                'estimated_budget' => 50000,
                'approved_budget' => 45000,
                'timeline_start' => now(),
                'timeline_end' => now()->addMonths(2),
                'approved_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);


        // PLANTING
        DB::table('planting_details')->insert([
            [
                'plantation_id' => 1,
                'planting_date' => now(),
                'actual_plant_count' => 180,
                'team_size' => 10,
                'attendance_verified' => true,
                'before_photos' => json_encode(['before1.jpg']),
                'after_photos' => json_encode(['after1.jpg']),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);


        // FENCING
        DB::table('fencing_details')->insert([
            [
                'plantation_id' => 1,
                'fence_type' => 'barbed',
                'material_used' => 'iron',
                'boundary_length_m' => 200,
                'installation_cost' => 12000,
                'verification_photos' => json_encode(['fence.jpg']),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);


        // OBSERVATION
        DB::table('observation_records')->insert([
            [
                'plantation_id' => 1,
                'observation_date' => now(),
                'survival_count' => 170,
                'survival_percentage' => 94.4,
                'avg_height_cm' => 35,
                'health_status' => 'healthy',
                'inspector_id' => 1,
                'photos' => json_encode(['obs1.jpg']),
                'remarks' => 'Plants growing well',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);


        // RELOCATION
        DB::table('relocation_records')->insert([
            [
                'plantation_id' => 1,
                'previous_location_id' => 1,
                'new_location_id' => 1,
                'reason' => 'Area adjustment',
                'relocated_by' => 1,
                'relocation_date' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

    }
}
