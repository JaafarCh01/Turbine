<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PlanningStatus; // Import if used in down() method

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('plannings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the table if rolling back (refer to original migration for exact schema)
        Schema::create('plannings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('turbineId')->constrained('turbines');
            $table->date('startDate');
            $table->date('endDate');
            $table->foreignUuid('createdBy')->constrained('users');
            $table->string('status')->default(PlanningStatus::DRAFT->value); // Adjust if PlanningStatus enum was deleted
            $table->timestamps();
        });
    }
};
