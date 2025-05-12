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
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('userId')->constrained('users');
            $table->uuidMorphs('commentable'); // Use targetId/targetType from UML, maps to commentable_id, commentable_type
            // $table->uuid('targetId');
            // $table->string('targetType');
            // $table->index(['targetId', 'targetType']); // Manual index if not using uuidMorphs
            $table->text('content');
            $table->timestamps(); // createdAt, updatedAt
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
