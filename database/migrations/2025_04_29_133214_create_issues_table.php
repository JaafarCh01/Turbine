<?php

use App\Enums\Severity;
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
        Schema::create('issues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('revisionId')->constrained('revisions');
            $table->string('description');
            $table->string('severity')->default(Severity::MEDIUM->value); // Enum as string
            $table->dateTime('reportedAt');
            $table->timestamps(); // createdAt, updatedAt
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
