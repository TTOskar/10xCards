<?php

namespace App\Enum;

enum StudyResult: string
{
    case KNOWN = 'known';
    case UNKNOWN = 'unknown';
    case POSTPONE = 'postpone';
} 