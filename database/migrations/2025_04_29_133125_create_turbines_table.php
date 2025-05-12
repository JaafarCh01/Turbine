<?php

use App\Enums\TurbineStatus;
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
        Schema::create('turbines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('location');
            $table->string('status')->default(TurbineStatus::ACTIVE->value); // Store enum as string
            $table->timestamps(); // Adds createdAt and updatedAt
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turbines');
    }
};
