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
        Schema::table('pdr_steps', function (Blueprint $table) {
            // Drop the existing foreign key constraint by column name
            $table->dropForeign(['pdrId']);

            // Re-add the foreign key constraint on the EXISTING pdrId column with onDelete('cascade')
            $table->foreign('pdrId') // Use $table->foreign() for existing columns
                  ->references('id')->on('pdrs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdr_steps', function (Blueprint $table) {
            // Drop the cascade constraint by column name
            $table->dropForeign(['pdrId']);

            // Re-add the original foreign key constraint (without cascade) on the EXISTING pdrId column
            $table->foreign('pdrId') // Use $table->foreign() for existing columns
                  ->references('id')->on('pdrs');
        });
    }
};
