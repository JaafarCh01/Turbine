<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Role; // Import the Role enum

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // WARNING: Changing primary key type on existing table is complex.
            // This assumes a new table or manual data migration.
            // If modifying existing table with data, more steps are needed.

            // Drop the old primary key if it exists (assuming it's 'id')
            // $table->dropPrimary('id'); // This might fail depending on DB state

            // Change 'id' to UUID. Ensure no auto-increment if it was set.
            // It's often easier to add a new UUID column, migrate data, drop old id, rename new column.
            // For simplicity here, we'll assume adding a UUID if 'id' wasn't already UUID.
            // If 'id' is the standard auto-incrementing integer:
            // $table->uuid('uuid')->primary(); // Add a new primary UUID key
            // Need to handle data migration from old 'id' to 'uuid'
            // And potentially drop the old 'id' column later.

            // For this example, let's *assume* we're modifying the column type directly
            // ** This will likely FAIL on tables with data or existing foreign keys **
            // $table->uuid('id')->change(); // Attempt to change type

            // Add Role enum column
            $table->string('role')->after('password')->default(Role::USER->value); // Add after password

            // Rename password column
            $table->renameColumn('password', 'passwordHash');

            // Ensure timestamps (usually present in default users table)
            if (!Schema::hasColumn('users', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            // IMPORTANT: If 'id' was the primary key, re-establish it after type change
            // if (!$table->hasPrimary('id')) { // Check if primary key exists
            //     $table->primary('id');
            // }

            // NOTE: A safer approach for existing tables involves more steps:
            // 1. Add new `uuid` column.
            // 2. Populate `uuid` for existing users.
            // 3. Update foreign keys in other tables to point to `uuid`.
            // 4. Drop old `id` primary key.
            // 5. Make `uuid` the primary key.
            // 6. Optionally rename `uuid` to `id`.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverse the renaming
            $table->renameColumn('passwordHash', 'password');

            // Remove the role column
            $table->dropColumn('role');

            // Reversing the primary key change is equally complex.
            // Would need to change type back to bigIncrements/integer and restore primary key.
            // $table->dropPrimary(); // Drop UUID primary key
            // $table->unsignedBigInteger('id')->change(); // Change type back
            // $table->primary('id'); // Restore integer primary key
            // Potentially drop the 'uuid' column if a new one was added in 'up()'.
        });
    }
};
