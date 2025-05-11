<?php

declare(strict_types=1);

namespace App\Enum\AI;

enum AiJobFlashcardStatus: string
{
    case ACCEPTED = 'accepted';
    case EDITED = 'edited';
    case REJECTED = 'rejected';
} 