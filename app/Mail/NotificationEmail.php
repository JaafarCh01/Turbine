<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class NotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $notification;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        $this->user = $notification->recipient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubjectByType($this->notification->type);

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address', 'noreply@turbinecare.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.notification',
            text: 'emails.notification-text',
            with: [
                'notification' => $this->notification,
                'user' => $this->user,
                'appName' => config('app.name', 'TurbineCare'),
                'appUrl' => config('app.url', 'http://localhost:8080'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get email subject based on notification type
     */
    private function getSubjectByType(string $type): string
    {
        $subjects = [
            'new_comment' => 'Nouveau commentaire - TurbineCare',
            'pdr_approved' => 'PDR approuvé - TurbineCare',
            'pdr_rejected' => 'PDR rejeté - TurbineCare',
            'pdr_created' => 'Nouveau PDR créé - TurbineCare',
            'revision_assigned' => 'Révision assignée - TurbineCare',
            'task_assigned' => 'Nouvelle tâche assignée - TurbineCare',
            'critical_issue' => '🚨 Alerte critique - TurbineCare',
        ];

        return $subjects[$type] ?? 'Nouvelle notification - TurbineCare';
    }
}
