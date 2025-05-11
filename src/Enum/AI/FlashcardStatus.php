<?php

declare(strict_types=1);

namespace App\Enum\AI;

enum FlashcardStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function isModifiable(): bool
    {
        return $this === self::PENDING;
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Review',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
        };
    }
} 