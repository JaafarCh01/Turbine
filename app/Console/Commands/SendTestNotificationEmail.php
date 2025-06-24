<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\NotificationService;

class SendTestNotificationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test-email {--user-id= : Specific user ID to send test email to} {--type=all : Type of notification to test (all, critical, pdr, comment)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test email notifications to verify email functionality';

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
        $type = $this->option('type');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found!");
                return 1;
            }
            $users = collect([$user]);
        } else {
            $users = User::limit(3)->get();
        }

        if ($users->isEmpty()) {
            $this->error('No users found in the database!');
            return 1;
        }

        $this->info('Sending test email notifications...');

        foreach ($users as $user) {
            if (!$user->email) {
                $this->warn("User {$user->name} has no email address, skipping...");
                continue;
            }

            $this->info("Sending to: {$user->name} ({$user->email})");

            if ($type === 'all' || $type === 'critical') {
                $this->notificationService->createNotification(
                    $user,
                    'critical_issue',
                    '🚨 TEST: Alerte critique détectée sur Turbine Alpha - Vibrations anormales dépassant les seuils de sécurité',
                    null,
                    true // Force email sending
                );
                $this->line('  ✓ Critical issue notification sent');
            }

            if ($type === 'all' || $type === 'pdr') {
                $this->notificationService->createNotification(
                    $user,
                    'pdr_approved',
                    'TEST: Votre PDR "Maintenance préventive turbine Beta" a été approuvé par le responsable technique',
                    null,
                    true
                );
                $this->line('  ✓ PDR approval notification sent');
            }

            if ($type === 'all' || $type === 'comment') {
                $this->notificationService->createNotification(
                    $user,
                    'new_comment',
                    'TEST: Un nouveau commentaire a été ajouté à votre PDR par Jean Dupont: "Vérification effectuée, tout est conforme."',
                    null,
                    true
                );
                $this->line('  ✓ Comment notification sent');
            }

            if ($type === 'all') {
                $this->notificationService->createNotification(
                    $user,
                    'revision_assigned',
                    'TEST: Une nouvelle révision vous a été assignée pour la turbine Gamma - Prévue pour le 15 décembre 2024',
                    null,
                    true
                );
                $this->line('  ✓ Revision assignment notification sent');
            }

            $this->newLine();
        }

        $this->info('All test email notifications have been sent!');
        $this->info('Check your email inbox and the Laravel logs for delivery status.');
        
        // Show mail configuration info
        $this->newLine();
        $this->info('Current mail configuration:');
        $this->line('Mail driver: ' . config('mail.default'));
        $this->line('From address: ' . config('mail.from.address'));
        $this->line('From name: ' . config('mail.from.name'));

        return 0;
    }
}
