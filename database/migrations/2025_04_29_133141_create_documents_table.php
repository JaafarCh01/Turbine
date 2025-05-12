<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\DocumentType;
use App\Enums\DocumentCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            // fileData: Storing file path/reference is better than blob usually
            $table->string('fileData'); // Or $table->text('fileData'); if path can be long
            $table->string('type'); // Enum stored as string
            $table->string('category'); // Enum stored as string
            $table->dateTime('uploadDate');
            $table->foreignUuid('uploadedBy')->constrained('users'); // Foreign key to users table
            $table->foreignUuid('turbineId')->constrained('turbines'); // Foreign key to turbines table
            $table->timestamps(); // Standard createdAt/updatedAt
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
