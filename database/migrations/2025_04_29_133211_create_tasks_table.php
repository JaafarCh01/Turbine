<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TaskStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('revisionId')->constrained('revisions')->onDelete('cascade');
            $table->string('description');
            $table->integer('ordre')->default(0);
            $table->string('status')->default(TaskStatus::TODO->value);
            $table->dateTime('plannedAt')->nullable(); // Assuming nullable
            $table->dateTime('doneAt')->nullable(); // Assuming nullable
            // No timestamps based on UML
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
