{{ $appName }} - Notification

Bonjour {{ $user->name }},

@switch($notification->type)
    @case('new_comment')
NOUVEAU COMMENTAIRE
    @break
    @case('pdr_approved')
PDR APPROUVÉ
    @break
    @case('pdr_rejected')
PDR REJETÉ
    @break
    @case('revision_assigned')
RÉVISION ASSIGNÉE
    @break
    @case('task_assigned')
TÂCHE ASSIGNÉE
    @break
    @case('critical_issue')
🚨 ALERTE CRITIQUE 🚨
    @break
    @case('pdr_created')
NOUVEAU PDR
    @break
    @default
NOTIFICATION
@endswitch

{{ $notification->message }}

Reçu le {{ $notification->created_at->format('d/m/Y à H:i') }}

---

Pour voir cette notification dans l'application :
{{ $appUrl }}/notifications

Pour gérer vos préférences de notification :
{{ $appUrl }}/settings

---

Cet email a été envoyé automatiquement par {{ $appName }}.
© {{ date('Y') }} {{ $appName }}. Tous droits réservés. 