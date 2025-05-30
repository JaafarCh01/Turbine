<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('planning_assignments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the table if rolling back (refer to original migration for exact schema)
        // This is a basic recreation, details might be needed from the original create_planning_assignments_table migration
        Schema::create('planning_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('planningId')->constrained('plannings')->cascadeOnDelete(); // Assuming cascade was there
            $table->foreignUuid('userId')->constrained('users')->cascadeOnDelete(); // Assuming cascade was there
            // Add any other columns that were in the original migration
            // $table->string('role')->nullable(); // Example if it had a role
            // $table->timestamps(); // If it had timestamps
        });
    }
};
