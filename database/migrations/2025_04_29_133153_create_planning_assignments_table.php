<?php

use App\Enums\Role;
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
        Schema::create('planning_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('planningId')->constrained('plannings');
            $table->foreignUuid('userId')->constrained('users');
            $table->string('roleDansPlanning'); // Enum Role as string
            // No timestamps based on UML

            // Optional: Add unique constraint for planningId and userId if needed
            // $table->unique(['planningId', 'userId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planning_assignments');
    }
};
