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
        Schema::create('pdr_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pdrId')->constrained('pdrs');
            $table->string('description');
            $table->boolean('mandatory')->default(false);
            $table->integer('ordre'); // Order/sequence
            // No timestamps based on UML

            // Add index for foreign key
            $table->index('pdrId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdr_steps');
    }
};
