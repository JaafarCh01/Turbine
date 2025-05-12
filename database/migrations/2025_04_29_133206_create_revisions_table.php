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
        Schema::create('revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('turbineId')->constrained('turbines');
            $table->dateTime('revisionDate');
            $table->foreignUuid('linkedPdrId')->nullable()->constrained('pdrs'); // Nullable link to PDR
            $table->foreignUuid('performedBy')->constrained('users');
            $table->timestamps(); // createdAt, updatedAt
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
