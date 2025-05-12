<?php

namespace App\Enums;

enum PDRStatus: string
{
    case DRAFT = 'DRAFT';
    case PENDING_APPROVAL = 'PENDING_APPROVAL';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
} 