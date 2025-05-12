<?php

use App\Enums\PDRStatus;
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
        Schema::create('pdrs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('turbineId')->constrained('turbines');
            $table->string('status')->default(PDRStatus::DRAFT->value); // Enum as string
            $table->foreignUuid('createdBy')->constrained('users');
            $table->foreignUuid('approverId')->nullable()->constrained('users'); // Nullable as per model
            $table->dateTime('approvedAt')->nullable();
            $table->timestamps(); // createdAt, updatedAt
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdrs');
    }
};
