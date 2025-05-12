<?php

use App\Enums\PlanningStatus;
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
        Schema::create('plannings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('turbineId')->constrained('turbines');
            $table->date('startDate');
            $table->date('endDate');
            $table->foreignUuid('createdBy')->constrained('users');
            $table->string('status')->default(PlanningStatus::DRAFT->value); // Enum as string
            $table->timestamps(); // createdAt, updatedAt
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};
