<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case APPROVER = 'APPROVER';
    case USER = 'USER';
    case CLIENT = 'CLIENT';
} 