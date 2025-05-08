<?php

namespace App\Enum;

enum JobStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
} 