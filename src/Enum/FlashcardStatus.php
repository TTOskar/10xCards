<?php

namespace App\Enum;

enum FlashcardStatus: string
{
    case ACCEPTED = 'accepted';
    case EDITED = 'edited';
    case REJECTED = 'rejected';
} 