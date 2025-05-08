<?php

namespace App\Enum;

enum UserAction: string
{
    case LOCK = 'lock';
    case UNLOCK = 'unlock';
} 