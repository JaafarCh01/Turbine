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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('recipientId')->constrained('users');
            $table->string('type'); // Notification type (e.g., 'new_comment', 'assignment')
            $table->text('message');
            $table->boolean('seen')->default(false);
            $table->uuidMorphs('entity'); // entityId, entityType
            $table->timestamp('createdAt')->nullable(); // Only createdAt based on UML
            // No default timestamps() to avoid updatedAt if not needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
