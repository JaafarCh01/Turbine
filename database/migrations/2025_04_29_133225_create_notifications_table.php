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
            
            // Manually define nullable morph columns
            $table->uuid('entity_id')->nullable();
            $table->string('entity_type')->nullable();
            $table->index(['entity_id', 'entity_type']); // Optional: add index if frequently queried

            $table->timestamps(); // Add standard timestamps
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
