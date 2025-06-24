<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Mail\NotificationEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a notification for a user
     */
    public function createNotification(
        User $recipient,
        string $type,
        string $message,
        ?Model $entity = null,
        bool $sendEmail = true
    ): Notification {
        $notification = Notification::create([
            'recipientId' => $recipient->id,
            'type' => $type,
            'message' => $message,
            'seen' => false,
            'entity_id' => $entity?->id,
            'entity_type' => $entity ? get_class($entity) : null,
        ]);

        // Send email notification if enabled
        if ($sendEmail && $recipient->email) {
            $this->sendEmailNotification($notification);
        }

        return $notification;
    }

    /**
     * Send email notification to the user
     */
    private function sendEmailNotification(Notification $notification): void
    {
        try {
            // Load the recipient relationship if not already loaded
            if (!$notification->recipient) {
                $notification->load('recipient');
            }

            Mail::to($notification->recipient->email)
                ->send(new NotificationEmail($notification));

            Log::info('Email notification sent successfully', [
                'notification_id' => $notification->id,
                'recipient_email' => $notification->recipient->email,
                'type' => $notification->type
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'notification_id' => $notification->id,
                'recipient_email' => $notification->recipient->email ?? 'unknown',
                'type' => $notification->type,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create notification for new comment
     */
    public function notifyNewComment(User $recipient, Model $commentableEntity, User $commenter, bool $sendEmail = true): Notification
    {
        $entityName = class_basename($commentableEntity);
        $message = "{$commenter->name} a ajouté un commentaire sur {$entityName} #{$commentableEntity->id}";
        
        return $this->createNotification($recipient, 'new_comment', $message, $commentableEntity, $sendEmail);
    }

    /**
     * Create notification for PDR approval
     */
    public function notifyPdrApproved(User $recipient, $pdr, User $approver): Notification
    {
        $message = "Votre PDR \"{$pdr->title}\" a été approuvé par {$approver->name}";
        
        return $this->createNotification($recipient, 'pdr_approved', $message, $pdr);
    }

    /**
     * Create notification for PDR rejection
     */
    public function notifyPdrRejected(User $recipient, $pdr): Notification
    {
        $message = "Votre PDR \"{$pdr->title}\" a été rejeté";
        
        return $this->createNotification($recipient, 'pdr_rejected', $message, $pdr);
    }

    /**
     * Create notification for revision assignment
     */
    public function notifyRevisionAssigned(User $recipient, $revision): Notification
    {
        $turbineName = $revision->turbine->name ?? 'Turbine inconnue';
        $message = "Une révision vous a été assignée pour la turbine {$turbineName}";
        
        return $this->createNotification($recipient, 'revision_assigned', $message, $revision);
    }

    /**
     * Create notification for task assignment
     */
    public function notifyTaskAssigned(User $recipient, $task): Notification
    {
        $message = "Une nouvelle tâche vous a été assignée: {$task->description}";
        
        return $this->createNotification($recipient, 'task_assigned', $message, $task);
    }

    /**
     * Create notification for critical issue
     */
    public function notifyCriticalIssue(User $recipient, $issue, bool $sendEmail = true): Notification
    {
        $turbineName = $issue->revision->turbine->name ?? 'Turbine inconnue';
        $message = "🚨 Alerte critique détectée sur {$turbineName}: {$issue->description}";
        
        // Critical issues should always send emails unless explicitly disabled
        return $this->createNotification($recipient, 'critical_issue', $message, $issue, $sendEmail);
    }

    /**
     * Create notification for new PDR creation
     */
    public function notifyPdrCreated(User $recipient, $pdr, User $creator): Notification
    {
        $message = "Un nouveau PDR \"{$pdr->title}\" a été créé par {$creator->name}";
        
        return $this->createNotification($recipient, 'pdr_created', $message, $pdr);
    }

    /**
     * Send notifications to multiple users
     */
    public function notifyMultipleUsers(array $recipients, string $type, string $message, ?Model $entity = null): array
    {
        $notifications = [];
        
        foreach ($recipients as $recipient) {
            if ($recipient instanceof User) {
                $notifications[] = $this->createNotification($recipient, $type, $message, $entity);
            }
        }
        
        return $notifications;
    }
} 