<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlantationWorkflowTables extends Migration
{
    public function up(): void
    {

        // GRIDS
        Schema::create('grids', function (Blueprint $table) {
            $table->id();
            $table->string('grid_code')->unique();
            $table->json('geo_polygon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });


        // PLANTATIONS
        Schema::create('plantations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('current_phase')->default('identification');
            $table->string('status')->default('active');

            $table->foreignId('grid_id')->nullable()->constrained('grids');

            // NO FK to users
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->index('grid_id');
            $table->index('current_phase');
            $table->index('status');

            $table->timestamps();
        });


        // PLANTATION LOCATIONS
        Schema::create('plantation_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plantation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->json('geo_polygon')->nullable();
            $table->decimal('center_lat', 10, 7)->nullable();
            $table->decimal('center_lng', 10, 7)->nullable();
            $table->decimal('area_sq_m', 12, 2)->nullable();

            $table->timestamp('verified_at')->nullable();

            // NO FK
            $table->unsignedInteger('created_by')->nullable();

            $table->index('plantation_id');

            $table->timestamps();
        });


        // PHASE LOGS
        Schema::create('plantation_phase_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plantation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('previous_phase');
            $table->string('new_phase');

            // NO FK
            $table->unsignedInteger('changed_by')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamp('changed_at')->nullable();

            $table->index('plantation_id');

            $table->timestamps();
        });


        // IDENTIFICATION
        Schema::create('identification_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plantation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('land_owner_name')->nullable();

            $table->enum('land_type', ['govt', 'private', 'community'])->nullable();

            $table->string('ownership_document')->nullable();

            $table->json('site_photos')->nullable();

            $table->boolean('is_verified')->default(false);

            $table->timestamps();
        });


        // MEASUREMENT
        Schema::create('measurement_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plantation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('total_area_sq_m', 12, 2)->nullable();
            $table->string('soil_type')->nullable();

            $table->boolean('water_source_available')->default(false);
            $table->string('slope_type')->nullable();

            $table->text('accessibility_notes')->nullable();
            $table->decimal('feasibility_score', 5, 2)->nullable();

            $table->timestamps();
        });


        // PLANNING
        Schema::create('planning_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plantation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('plant_species')->nullable();
            $table->integer('plant_count')->nullable();

            $table->string('spacing_pattern')->nullable();

            $table->decimal('estimated_budget', 12, 2)->nullable();
            $table->decimal('approved_budget', 12, 2)->nullable();

            $table->date('timeline_start')->nullable();
            $table->date('timeline_end')->nullable();

            // NO FK
            $table->unsignedInteger('approved_by')->nullable();

            $table->timestamps();
        });


        // PLANTING
        Schema::create('planting_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plantation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('planting_date')->nullable();
            $table->integer('actual_plant_count')->nullable();
            $table->integer('team_size')->nullable();

            $table->boolean('attendance_verified')->default(false);

            $table->json('before_photos')->nullable();
            $table->json('after_photos')->nullable();

            $table->timestamps();
        });


        // FENCING
        Schema::create('fencing_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plantation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('fence_type')->nullable();
            $table->string('material_used')->nullable();

            $table->decimal('boundary_length_m', 12, 2)->nullable();
            $table->decimal('installation_cost', 12, 2)->nullable();

            $table->json('verification_photos')->nullable();

            $table->timestamps();
        });


        // OBSERVATION
        Schema::create('observation_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plantation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('observation_date');

            $table->integer('survival_count')->nullable();
            $table->decimal('survival_percentage', 5, 2)->nullable();

            $table->decimal('avg_height_cm', 8, 2)->nullable();
            $table->string('health_status')->nullable();

            // NO FK
            $table->unsignedInteger('inspector_id')->nullable();

            $table->json('photos')->nullable();
            $table->text('remarks')->nullable();

            $table->index('plantation_id');
            $table->index('observation_date');

            $table->timestamps();
        });


        // RELOCATION
        Schema::create('relocation_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plantation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('previous_location_id')->nullable();
            $table->unsignedBigInteger('new_location_id')->nullable();

            $table->foreign('previous_location_id')
                ->references('id')
                ->on('plantation_locations')
                ->nullOnDelete();

            $table->foreign('new_location_id')
                ->references('id')
                ->on('plantation_locations')
                ->nullOnDelete();

            $table->text('reason')->nullable();

            // NO FK
            $table->unsignedInteger('relocated_by')->nullable();

            $table->date('relocation_date')->nullable();

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('relocation_records');
        Schema::dropIfExists('observation_records');
        Schema::dropIfExists('fencing_details');
        Schema::dropIfExists('planting_details');
        Schema::dropIfExists('planning_details');
        Schema::dropIfExists('measurement_details');
        Schema::dropIfExists('identification_details');
        Schema::dropIfExists('plantation_phase_logs');
        Schema::dropIfExists('plantation_locations');
        Schema::dropIfExists('plantations');
        Schema::dropIfExists('grids');
    }
}
