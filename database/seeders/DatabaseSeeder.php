<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Turbine;
use App\Models\Document;
use App\Models\PDR;
use App\Models\PdrStep;
use App\Models\Revision;
use App\Models\Task;
use App\Models\Issue;
use App\Models\Comment;
use App\Models\Notification;
use App\Enums\Role; // Import Role enum
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Import Hash

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create specific users with roles
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN,
            'passwordHash' => Hash::make('password'), // Explicitly hash password
        ]);

        $approverUser = User::factory()->create([
            'name' => 'Approver User',
            'email' => 'approver@example.com',
            'role' => Role::APPROVER,
            'passwordHash' => Hash::make('password'),
        ]);

        $normalUser = User::factory()->create([
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'role' => Role::USER,
            'passwordHash' => Hash::make('password'),
        ]);

        $clientUser = User::factory()->create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'role' => Role::CLIENT,
            'passwordHash' => Hash::make('password'),
        ]);

        // Create some Turbines
        $turbines = Turbine::factory(5)->create();

        // Create Documents, assigning random turbines and users
        Document::factory(10)->recycle([$adminUser, $normalUser]) // Use existing users for uploader
                             ->recycle($turbines) // Use existing turbines
                             ->create();

        // Create PDRs, Steps, Revisions, Tasks, Issues, Comments
        PDR::factory(6)
            ->recycle($turbines)
            ->recycle([$normalUser, $adminUser]) // createdBy
            // Use state to ensure approved PDRs have an approver
            ->state(function (array $attributes) use ($approverUser) {
                 if ($attributes['status'] === \App\Enums\PDRStatus::APPROVED || $attributes['status'] === \App\Enums\PDRStatus::REJECTED) {
                     return ['approverId' => $approverUser->id];
                 }
                 return [];
            })
            ->has(PdrStep::factory()->count(fake()->numberBetween(3, 8)), 'steps') // Create steps for each PDR
            ->has(Comment::factory()->count(fake()->numberBetween(0, 3)) // Add 0-3 comments per PDR
                ->recycle([$normalUser, $approverUser, $adminUser]), 'comments') // Polymorphic relation name
            ->afterCreating(function (PDR $pdr) use ($normalUser, $approverUser, $adminUser) {
                // Create Revisions sometimes linked to PDRs
                if (fake()->boolean(80)) { // 80% chance to create a revision for a PDR
                     Revision::factory()
                         ->for($pdr->turbine) // Use the same turbine as the PDR
                         ->create([
                             'linkedPdrId' => $pdr->id,
                             'performedBy' => $approverUser->id, // Example: Approver performs revision
                             'revisionDate' => fake()->dateTimeBetween($pdr->createdAt, '+1 month'),
                         ])
                         ->each(function (Revision $revision) use ($normalUser, $adminUser, $approverUser) {
                             // Add Tasks to Revision
                             Task::factory(fake()->numberBetween(2, 6))
                                 ->for($revision)
                                 ->create();
                             // Add Issues to Revision
                             Issue::factory(fake()->numberBetween(0, 2))
                                 ->for($revision)
                                 ->create();
                             // Add Comments to Revision
                             Comment::factory(fake()->numberBetween(0, 2))
                                  ->recycle([$normalUser, $adminUser, $approverUser])
                                  ->for($revision, 'commentable') // Specify polymorphic relation
                                  ->create();
                         });
                }
            })
            ->create();

         // Create some standalone Revisions not linked to PDRs
         Revision::factory(3)
             ->recycle($turbines)
             ->recycle([$normalUser, $approverUser]) // performedBy
             ->has(Task::factory()->count(fake()->numberBetween(2, 5)))
             ->has(Issue::factory()->count(fake()->numberBetween(0, 1)))
             ->has(Comment::factory()->count(1)->recycle([$adminUser]), 'comments')
             ->create(['linkedPdrId' => null]); // Ensure not linked

        // Create Notifications for users
        $usersForNotifications = [$adminUser, $approverUser, $normalUser];
        foreach ($usersForNotifications as $user) {
            Notification::factory(fake()->numberBetween(2, 5))->create([
                'recipientId' => $user->id,
                'message' => 'This is a test notification for you: ' . fake()->sentence(4)
            ]);

            // Example of a more specific notification type if needed
            if ($user->role === Role::APPROVER) {
                Notification::factory()->create([
                    'recipientId' => $user->id,
                    'type' => 'status_update',
                    'message' => 'A PDR is awaiting your approval.',
                    'seen' => false,
                ]);
            }
        }
    }
}
