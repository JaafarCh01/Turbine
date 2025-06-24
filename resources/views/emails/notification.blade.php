<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} - Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e3f2fd;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 10px;
        }
        .notification-icon {
            font-size: 48px;
            margin: 20px 0;
        }
        .notification-content {
            background-color: #f8f9fa;
            border-left: 4px solid #1976d2;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .notification-message {
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
        }
        .notification-type {
            display: inline-block;
            background-color: #1976d2;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .cta-button {
            display: inline-block;
            background-color: #1976d2;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .cta-button:hover {
            background-color: #1565c0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }
        .footer a {
            color: #1976d2;
            text-decoration: none;
        }
        .timestamp {
            color: #888;
            font-size: 14px;
            margin-top: 15px;
        }
        .critical {
            border-left-color: #d32f2f;
        }
        .critical .notification-type {
            background-color: #d32f2f;
        }
        .critical .cta-button {
            background-color: #d32f2f;
        }
        .critical .cta-button:hover {
            background-color: #c62828;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">🏭 {{ $appName }}</div>
            <div class="notification-icon">
                @switch($notification->type)
                    @case('new_comment')
                        💬
                        @break
                    @case('pdr_approved')
                        ✅
                        @break
                    @case('pdr_rejected')
                        ❌
                        @break
                    @case('revision_assigned')
                        📋
                        @break
                    @case('task_assigned')
                        📝
                        @break
                    @case('critical_issue')
                        🚨
                        @break
                    @case('pdr_created')
                        📄
                        @break
                    @default
                        🔔
                @endswitch
            </div>
        </div>

        <div class="notification-content {{ $notification->type === 'critical_issue' ? 'critical' : '' }}">
            <div class="notification-type">
                @switch($notification->type)
                    @case('new_comment')
                        Nouveau commentaire
                        @break
                    @case('pdr_approved')
                        PDR approuvé
                        @break
                    @case('pdr_rejected')
                        PDR rejeté
                        @break
                    @case('revision_assigned')
                        Révision assignée
                        @break
                    @case('task_assigned')
                        Tâche assignée
                        @break
                    @case('critical_issue')
                        Alerte critique
                        @break
                    @case('pdr_created')
                        Nouveau PDR
                        @break
                    @default
                        Notification
                @endswitch
            </div>

            <div class="notification-message">
                Bonjour {{ $user->name }},<br><br>
                {{ $notification->message }}
            </div>

            <div class="timestamp">
                Reçu le {{ $notification->created_at->format('d/m/Y à H:i') }}
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ $appUrl }}/notifications" class="cta-button">
                Voir dans l'application
            </a>
        </div>

        <div class="footer">
            <p>
                Cet email a été envoyé automatiquement par {{ $appName }}.<br>
                <a href="{{ $appUrl }}/settings">Gérer mes préférences de notification</a>
            </p>
            <p>
                © {{ date('Y') }} {{ $appName }}. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html> 