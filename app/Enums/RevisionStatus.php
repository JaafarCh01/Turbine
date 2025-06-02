<?php

namespace App\Enums;

enum RevisionStatus: string
{
    case PENDING = 'pending';
    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
} 