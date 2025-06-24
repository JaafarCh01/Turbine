<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\PDR;
use App\Models\Turbine;
use App\Services\NotificationService;

class CreateTestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:create-test {--user-id= : Specific user ID to create notifications for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test notifications for development and testing';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found!");
                return 1;
            }
            $users = collect([$user]);
        } else {
            $users = User::limit(5)->get();
        }

        if ($users->isEmpty()) {
            $this->error('No users found in the database!');
            return 1;
        }

        $this->info('Creating test notifications...');

        foreach ($users as $user) {
            // Create various types of test notifications
            $this->notificationService->createNotification(
                $user,
                'new_comment',
                'Un nouveau commentaire a été ajouté sur votre PDR "Test Maintenance Turbine Alpha"'
            );

            $this->notificationService->createNotification(
                $user,
                'pdr_approved',
                'Votre PDR "Révision trimestrielle" a été approuvé par Admin User'
            );

            $this->notificationService->createNotification(
                $user,
                'critical_issue',
                '🚨 Alerte critique détectée sur Turbine Beta: Vibrations anormales détectées'
            );

            $this->notificationService->createNotification(
                $user,
                'revision_assigned',
                'Une révision vous a été assignée pour la turbine Gamma'
            );

            $this->notificationService->createNotification(
                $user,
                'task_assigned',
                'Une nouvelle tâche vous a été assignée: Vérifier les connexions électriques'
            );

            $this->info("Created 5 test notifications for user: {$user->name}");
        }

        $this->info('Test notifications created successfully!');
        return 0;
    }
}
