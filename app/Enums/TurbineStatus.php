<?php

namespace App\Enums;

enum TurbineStatus: string
{
    case ACTIVE = 'ACTIVE';
    case MAINTENANCE = 'MAINTENANCE';
    case INACTIVE = 'INACTIVE';
} 